<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector\StoreQueryBuilder;

use Medas\Core\{Attributes\Service, Types\Collection};
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\{Definition, DerivedValues\RelatedRows, Operants\Property};
use Medas\PdoStorage\Exceptions\{RelatedRowsInOtherStorage, UnsupportedInRelatedRows};
use Medas\StorageManager\StorageManager;

/**
 * Opens and closes the correlated subquery of an aggregate over related rows (CountOf,
 * SumOf, ValueOf, GroupConcatOf). CalculationsProcessor writes what it selects, its
 * conditions and sorts in the job this returns.
 *
 * The subquery has a job of its own: the related entity is its main entity, under an
 * alias numbered per query, so a bare property in its definition names the related
 * entity's column, and nesting or using the same store twice cannot clash. Constants
 * and arguments are shared with the outer job, so their placeholders stay unique.
 */
#[Service]
readonly class RelatedRowsCompiler
{
    private const string ALIAS_SUFFIX = '__related';

    public function __construct(
        private CollectionContainsCompiler $collectionContainsCompiler,
        private MetaDataManager            $metaDataManager,
        private StorageManager             $storageManager,
    )
    {
    }

    // The subquery's job, its query holding "from <store> as <alias> where <correlation>"
    public function open(Job $job, RelatedRows $rows): Job
    {
        $definition = $rows->definition;
        $metaData = $this->metaDataManager->get($definition->entity);

        $this->refuseUnsupported($definition);

        if ($metaData->inheritance->parent !== null) {
            throw new UnsupportedInRelatedRows($definition->entity, 'class-table inheritance');
        }

        if ($this->storageManager->byName($metaData->entity->storage) !== $job->database) {
            throw new RelatedRowsInOtherStorage($definition->entity);
        }

        $quote = fn(string $name) => $job->driverHandler->quote($job->database, $name);
        $subJob = new Job($job->database, $job->driverHandler, $definition->entity);

        $this->share($job, $subJob);

        $subJob->subqueryCount = $job->subqueryCount + 1;
        $alias = $quote($metaData->entity->store . self::ALIAS_SUFFIX . $subJob->subqueryCount);
        $subJob->stores = [$definition->entity => $alias];

        $subJob->query = ' from '
            . $quote($metaData->entity->store)
            . ' as '
            . $alias
            . ' where '
            . $this->correlation($subJob, $metaData, $rows, $this->outerColumn($job, $rows));

        return $subJob;
    }

    // Only conditions and sorts have a meaning inside the subquery; anything else would
    // be left out without a word
    private function refuseUnsupported(Definition $definition): void
    {
        $unsupported = [
            'relations' => $definition->relations,
            'groupings' => $definition->groupings,
            'parameters' => $definition->parameters,
            'output values' => $definition->outputValues,
            'derived values' => $definition->derivedValues,
            'a slice' => $definition->slice === null ? [] : [$definition->slice],
        ];

        foreach ($unsupported as $what => $elements) {
            if ($elements !== []) {
                throw new UnsupportedInRelatedRows($definition->entity, $what);
            }
        }
    }

    // A related row refers to the selected row through its own column, or, through a
    // collection property, by holding it in that property's join table
    private function correlation(Job $subJob, MetaData $metaData, RelatedRows $rows, string $outerColumn): string
    {
        if ($metaData->property($rows->referringProperty)->type instanceof Collection) {
            return $this->collectionContainsCompiler->compile(
                $subJob,
                Property::c($rows->referringProperty),
                $outerColumn
            );
        }

        return $subJob->stores[$subJob->mainEntity]
            . '.'
            . $subJob->driverHandler->quote($subJob->database, $rows->referringProperty)
            . ' = '
            . $outerColumn;
    }

    private function outerColumn(Job $job, RelatedRows $rows): string
    {
        $property = $rows->property ?? $this->metaDataManager->get($job->mainEntity)->idProperty->name;

        return $job->storeForProperty(null, $property)
            . '.'
            . $job->driverHandler->quote($job->database, $property);
    }

    // Hands what the subquery added to the constants and arguments back to the outer job
    public function close(Job $job, Job $subJob): void
    {
        $this->share($subJob, $job);
    }

    private function share(Job $from, Job $to): void
    {
        $to->foundArguments = $from->foundArguments;
        $to->argumentOccurrences = $from->argumentOccurrences;
        $to->argumentLikeWrappers = $from->argumentLikeWrappers;
        $to->foundConstants = $from->foundConstants;
        $to->variableSizedParameters = $from->variableSizedParameters;
        $to->subqueryCount = $from->subqueryCount;
    }
}
