<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\Core\Interfaces\NotCacheable;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\{Conditions\Condition,
    Conditions\WhereIs,
    Conditions\WhereIsAtLeast,
    Conditions\WhereIsAtMost,
    Conditions\WhereIsLessThan,
    Conditions\WhereIsMoreThan,
    Conditions\WhereIsNotNull,
    Conditions\WhereIsNull,
    Exceptions\UndeclaredParameters,
    Exceptions\UnhandledConditionType,
    Exceptions\UnhandledOperantType,
    Exceptions\UnhandledRelationType,
    Exceptions\UnhandledSortType,
    Operants\Argument,
    Operants\Operant,
    Operants\Property,
    Operants\Value,
    Parameter,
    Relations\Relation,
    Selector,
    Sorting\SortBy};
use Medas\PdoStorage\Database;
use Medas\PdoStorage\Drivers\{DriverHandler, Interfaces\SelectQueryBuilder};
use Medas\PdoStorage\Exceptions\StorageIsNotDatabase;
use Medas\PdoStorage\Queries\{ParameterizedQuery, Query};
use Medas\PdoStorage\ValueSerializer;
use Medas\ServiceManager\Cache\CacheManager;

class BaseSelectQueryBuilder implements SelectQueryBuilder
{
    private string $query;
    private array $stores;
    private array $foundArguments;
    private array $foundConstants;
    private string $mainEntity;

    public function __construct(
        private readonly CacheManager    $cacheManager,
        private readonly DriverHandler   $driver,
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
            /** @var ParameterizedQuery $paraQuery */
            $paraQuery = $this->cacheManager->get()->get(
                [static::class, $selector::class],
                fn() => $this->process($selector)
            );
        }

        return $this->compileQuery($paraQuery, $arguments);
    }

    private function process(Selector $selector): ParameterizedQuery
    {
        $definition = $selector->definition();
        $metaData = $this->metaDataManager->get($definition->entity);

        $database = storage($metaData->entity->storage);

        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if (!$database instanceof Database) {
            throw new StorageIsNotDatabase($metaData->entity->storage);
        }

        $this->mainEntity = $metaData->className;

        $quotedMainStore = $this->driver->quote($metaData->entity->store);
        $this->stores = [$this->mainEntity => $quotedMainStore];
        $this->foundArguments = [];
        $this->foundConstants = [];

        $this->query = 'select * from ' . $quotedMainStore;

        $this->processRelations($definition->relations);
        $this->processConditions($definition->conditions);
        $this->processSorting($definition->sorts);
        $this->processParameters($definition->parameters);

        return new ParameterizedQuery($this->query, $definition->parameters, $this->foundConstants, $database);
    }

    /** @param Relation[] $relations */
    private function processRelations(array $relations): void
    {
        foreach ($relations as $relation) {
            throw new UnhandledRelationType($relation);
        }
    }

    /** @param Condition[] $conditions */
    private function processConditions(array $conditions): void
    {
        if ($conditions) {
            $this->query .= ' where ';
        }

        $isFirstCondition = true;

        foreach ($conditions as $condition) {
            if (!$isFirstCondition) {
                $this->query .= ' and ';
            }

            match ($condition::class) {
                WhereIs::class => $this->processComparison($condition, '='),
                WhereIsMoreThan::class => $this->processComparison($condition, '>'),
                WhereIsLessThan::class => $this->processComparison($condition, '<'),
                WhereIsAtLeast::class => $this->processComparison($condition, '>='),
                WhereIsAtMost::class => $this->processComparison($condition, '<='),
                WhereIsNull::class => $this->processNullComparison($condition, true),
                WhereIsNotNull::class => $this->processNullComparison($condition, false),
                default => throw new UnhandledConditionType($condition),
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
                . $this->driver->quote($operant->name);
        }

        if ($operant instanceof Argument) {
            $this->foundArguments[$operant->name] = true;
            return ':' . $operant->name;
        }

        if ($operant instanceof Value) {
            $operant->value = service(ValueSerializer::class)->serialize($operant->value);
            $name = sha1(serialize($operant->value));
            $this->foundConstants[$name] = $operant->value;
            return ':' . $name;
        }

        throw new UnhandledOperantType($operant);
    }

    private function processNullComparison(WhereIsNull $condition, bool $isNull): void
    {
        $this->query .= $this->operantToQuery($condition->property)
            . ($isNull ? ' is null' : ' is not null');
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

            throw new UnhandledSortType($sort);
        }

        if ($parts) {
            $this->query .= ' order by ' . implode(', ', $parts);
        }
    }

    private function processParameters(array $parameters): void
    {
        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {
            unset($this->foundArguments[$parameter->name]);
        }

        if ($this->foundArguments) {
            throw new UndeclaredParameters(array_keys($this->foundArguments));
        }
    }

    private function compileQuery(ParameterizedQuery $paraQuery, array $arguments): Query
    {
        $query = new Query($paraQuery->query, $paraQuery->constants, $paraQuery->database);

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
