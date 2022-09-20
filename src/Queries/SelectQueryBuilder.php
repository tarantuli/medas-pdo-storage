<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\{Conditions\Condition,
    Conditions\WhereIs,
    Conditions\WhereIsAtLeast,
    Conditions\WhereIsAtMost,
    Conditions\WhereIsLessThan,
    Conditions\WhereIsMoreThan,
    Conditions\WhereIsNotNull,
    Conditions\WhereIsNull,
    Exceptions\UndeclaredParametersException,
    Exceptions\UnhandledConditionTypeException,
    Exceptions\UnhandledOperantTypeException,
    Exceptions\UnhandledRelationTypeException,
    Exceptions\UnhandledSortTypeException,
    Operants\Argument,
    Operants\Operant,
    Operants\Property,
    Operants\Value,
    Parameter,
    Relations\Relation,
    Selector,
    Sorting\SortBy
};
use Medas\PdoStorage\Database;
use Medas\PdoStorage\Exceptions\StorageIsNotDatabaseException;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Cache\{CacheManager, Interfaces\NotCacheable};
use Medas\StorageManager\Entities\SelectorActionBuilder;

#[Service]
class SelectQueryBuilder implements SelectorActionBuilder
{
    private string $query;
    private array $stores;
    private array $foundArguments;
    private array $foundConstants;
    private string $mainEntity;
    private Database $database;

    public function __construct(
        private readonly CacheManager    $cacheManager,
        private readonly MetaDataManager $metaDataManager,
    )
    {
    }

    public function build(Selector $selector, array $arguments): Query
    {
        if ($selector instanceof NotCacheable) {
            $paraQuery = $this->process($selector);
        }
        else {
            /** @var ParaQuery $paraQuery */
            $paraQuery = $this->cacheManager->get()->get(
                [static::class, $selector::class],
                fn() => $this->process($selector)
            );
        }

        return $this->compileQuery($paraQuery, $arguments);
    }

    private function process(Selector $selector): ParaQuery
    {
        $definition = $selector->get();
        $metaData = $this->metaDataManager->get($definition->entity);

        $database = storage($metaData->entity->storage);

        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if (!$database instanceof Database) {
            throw new StorageIsNotDatabaseException($metaData->entity->storage);
        }

        $this->database = $database;
        $this->mainEntity = $metaData->className;

        $quotedMainStore = $this->database->quoteIdentifier($metaData->entity->store);
        $this->stores = [$this->mainEntity => $quotedMainStore];
        $this->foundArguments = [];
        $this->foundConstants = [];

        $this->query = 'SELECT * FROM ' . $quotedMainStore;

        $this->processRelations($definition->relations);
        $this->processConditions($definition->conditions);
        $this->processSorting($definition->sorts);
        $this->processParameters($definition->parameters);

        return new ParaQuery($this->query, $definition->parameters, $this->foundConstants, $this->database);
    }

    /** @param Relation[] $relations */
    private function processRelations(array $relations): void
    {
        foreach ($relations as $relation) {
            throw new UnhandledRelationTypeException($relation);
        }
    }

    /** @param Condition[] $conditions */
    private function processConditions(array $conditions): void
    {
        if ($conditions) {
            $this->query .= ' WHERE ';
        }

        $isFirstCondition = true;

        foreach ($conditions as $condition) {

            if (!$isFirstCondition) {
                $this->query .= ' AND ';
            }

            match ($condition::class) {
                WhereIs::class => $this->processComparison($condition, '='),
                WhereIsMoreThan::class => $this->processComparison($condition, '>'),
                WhereIsLessThan::class => $this->processComparison($condition, '<'),
                WhereIsAtLeast::class => $this->processComparison($condition, '>='),
                WhereIsAtMost::class => $this->processComparison($condition, '<='),
                WhereIsNull::class => $this->processNullComparison($condition, true),
                WhereIsNotNull::class => $this->processNullComparison($condition, false),
                default => throw new UnhandledConditionTypeException($condition),
            };

            $isFirstCondition = false;
        }
    }

    private function processComparison(WhereIs $condition, string $operator): void
    {
        $this->query .= $this->operantToQuery($condition->property)
            . $operator
            . $this->operantToQuery($condition->value);
    }

    private function operantToQuery(Operant $operant): string
    {
        if ($operant instanceof Property) {
            return $this->stores[$operant->entity ?? $this->mainEntity]
                . '.'
                . $this->database->quoteIdentifier($operant->name);
        }

        if ($operant instanceof Argument) {
            $this->foundArguments[$operant->name] = true;
            return ':' . $operant->name;
        }

        if ($operant instanceof Value) {
            $name = sha1(serialize($operant->value));
            $this->foundConstants[$name] = $operant->value;
            return ':' . $name;
        }

        throw new UnhandledOperantTypeException($operant);
    }

    private function processNullComparison(WhereIsNull $condition, bool $isNull): void
    {
        $this->query .= $this->operantToQuery($condition->property)
            . ($isNull ? ' IS NULL' : ' IS NOT NULL');
    }

    /** @param SortBy[] $sorts */
    private function processSorting(array $sorts): void
    {
        $parts = [];
        foreach ($sorts as $sort) {
            if ($sort instanceof SortBy && $sort->operant instanceof Property) {
                $parts[] = $sort->operant->name . ' ' . $sort->direction->name;
                continue;
            }

            throw new UnhandledSortTypeException($sort);
        }

        if ($parts) {
            $this->query .= ' ORDER BY ' . implode(', ', $parts);
        }
    }

    private function processParameters(array $parameters): void
    {
        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {
            unset($this->foundArguments[$parameter->name]);
        }

        if ($this->foundArguments) {
            throw new UndeclaredParametersException(array_keys($this->foundArguments));
        }
    }

    private function compileQuery(ParaQuery $paraQuery, array $arguments): Query
    {
        $query = new Query($paraQuery->query, $paraQuery->constants, $paraQuery->database);

        /** @var Parameter $parameter */
        foreach ($paraQuery->parameters as $parameter) {
            if (array_key_exists($parameter->name, $arguments)) {
                $value = $arguments[$parameter->name];
            }
            elseif ($parameter->hasDefault) {
                $value = $parameter->default;
            }
            else {
                throw new \Exception('no value given for parameter ' . $parameter->name);
            }

            $query->arguments[$parameter->name] = $value;
        }

        return $query;
    }
}
