<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\Definition;
use Medas\PdoStorage\{Database, Events\DatabaseControllerRequest};

#[Service]
readonly class StoreQueryBuilder
{
    public function __construct(
        private StoreQueryBuilder\CalculationsProcessor $calculationsProcessor,
        private StoreQueryBuilder\GroupingProcessor     $groupingProcessor,
        private StoreQueryBuilder\OutputValuesProcessor $outputValuesProcessor,
        private StoreQueryBuilder\ParametersProcessor   $parametersProcessor,
        private StoreQueryBuilder\RelationsProcessor    $relationsProcessor,
        private StoreQueryBuilder\SliceProcessor        $sliceProcessor,
        private StoreQueryBuilder\SortingProcessor      $sortingProcessor,
    )
    {
    }

    public function buildQuery(
        Database   $database,
        Definition $definition,
        string     $storeName,
        string     $entityName = '',
        bool       $isCount = false,
        array      $parentStores = [],
        array      $propertyStores = [],
        string     $idProperty = 'id'
    ): ParameterizedQuery
    {
        $controllerRequest = dispatch(new DatabaseControllerRequest($database));

        $job = new StoreQueryBuilder\Job(
            $database,
            $controllerRequest->databaseController->driverHandler,
            $entityName,
        );

        $quotedMainStore = $job->driverHandler->quote($database, $storeName);
        $job->stores = [$job->mainEntity => $quotedMainStore];

        $inheritanceJoins = $this->applyInheritanceStores(
            $database,
            $job,
            $quotedMainStore,
            $parentStores,
            $propertyStores,
            $idProperty
        );

        $this->outputValuesProcessor->process($job, $definition->outputValues);

        if ($isCount) {
            $outputValues = 'count(*) as ' . $job->driverHandler->quote($database, 'count');
        }
        else {
            $outputValues = $job->outputValues ? implode(', ', $job->outputValues) : '*';
        }

        $job->query = sprintf('select %s from %s', $outputValues, $quotedMainStore);
        $job->query .= $inheritanceJoins;

        $this->relationsProcessor->process($job, $definition->relations);

        if ($definition->conditions) {
            $this->calculationsProcessor->process($job, $definition->conditions);

            $job->query .= ' where ' . $job->currentCalculation;
        }

        $this->groupingProcessor->process($job, $definition->groupings);
        $this->sortingProcessor->process($job, $definition->sorts);

        $usedParameters = $this->parametersProcessor->process($job, $definition->parameters);

        if (!$isCount) {
            $this->sliceProcessor->process($job, $definition->slice);
        }

        return new ParameterizedQuery(
            $job->query,
            $usedParameters,
            $job->foundConstants,
            $job->variableSizedParameters,
            $job->argumentOccurrences,
            $job->argumentLikeWrappers,
            $database,
        );
    }

    private function applyInheritanceStores(
        Database              $database,
        StoreQueryBuilder\Job $job,
        string                $quotedMainStore,
        array                 $parentStores,
        array                 $propertyStores,
        string                $idProperty
    ): string
    {
        foreach ($propertyStores as $propertyName => $storeName) {
            $job->propertyStores[$propertyName] = $job->driverHandler->quote($database, $storeName);
        }

        if (!$parentStores) {
            return '';
        }

        $quotedId = $job->driverHandler->quote($database, $idProperty);
        $joins = '';

        foreach ($parentStores as $parentStore) {
            $quotedParent = $job->driverHandler->quote($database, $parentStore);

            $joins .= ' join '
                . $quotedParent
                . ' on ('
                . $quotedParent
                . '.'
                . $quotedId
                . ' = '
                . $quotedMainStore
                . '.'
                . $quotedId
                . ')';
        }

        return $joins;
    }

    public function buildCountQuery(
        Database   $database,
        Definition $definition,
        string     $storeName,
        string     $entityName = '',
    ): ParameterizedQuery
    {
        $request = dispatch(new DatabaseControllerRequest($database));

        $job = new StoreQueryBuilder\Job(
            $database,
            $request->databaseController->driverHandler,
            $entityName,
        );

        $quotedMainStore = $job->driverHandler->quote($database, $storeName);
        $job->stores = [$job->mainEntity => $quotedMainStore];
        $job->query = 'select count(*) as rowCount from ' . $quotedMainStore;

        $this->relationsProcessor->process($job, $definition->relations);

        if ($definition->conditions) {
            $this->calculationsProcessor->process($job, $definition->conditions);

            $job->query .= ' where ' . $job->currentCalculation;
        }

        $usedParameters = $this->parametersProcessor->process($job, $definition->parameters);

        return new ParameterizedQuery(
            $job->query,
            $usedParameters,
            $job->foundConstants,
            $job->variableSizedParameters,
            $job->argumentOccurrences,
            $job->argumentLikeWrappers,
            $database,
        );
    }
}
