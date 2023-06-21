<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\Core\Interfaces\ManagedCollection;
use Medas\EntityManager\Filters\{Between, LessThan, MoreThan};
use Medas\EntityManager\Selector\Selector;
use Medas\EntityManager\Types\Collection;
use Medas\PdoStorage\Database;
use Medas\PdoStorage\Drivers\{Handler, Interfaces\QueryBuilder};
use Medas\PdoStorage\JoinTableManager;
use Medas\PdoStorage\Queries\{Query, QueryCollection};
use Medas\PdoStorage\Table;
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\{Action, Priority};

abstract class BaseQueryBuilder implements QueryBuilder
{
    protected string $query;
    protected array $arguments;

    public function __construct(
        protected readonly Handler  $driver,
        protected readonly Database $database,
    )
    {
    }

    /** @param Table[] $tables */
    public function select(array $tables, array $filters): QueryCollection
    {
        $this->arguments = [];
        $this->query = /** @lang text */
            'select * from ';

        foreach ($tables as $table) {
            $this->query .= $this->driver->quote($table->name) . ',';
        }

        $this->query = substr($this->query, 0, -1);

        if ($filters) {
            $this->query .= ' where ';
            $this->appendConditions($filters);
        }

        return QueryCollection::fromQuery(new Query($this->query, $this->arguments, $this->database));
    }

    /** @noinspection PhpSameParameterValueInspection */
    private function appendConditions(array $filters, string $separator = 'and'): void
    {
        foreach ($filters as $field => $value) {
            if ($value instanceof LessThan) {
                $this->query .= $this->driver->quote($value->field) . ' < ? ' . $separator . ' ';
                $this->arguments[] = $value->value;
            }
            elseif ($value instanceof MoreThan) {
                $this->query .= $this->driver->quote($value->field) . ' > ? ' . $separator . ' ';
                $this->arguments[] = $value->value;
            }
            elseif ($value instanceof Between) {
                $this->query .= $this->driver->quote($value->field) . 'between ? and ? ' . $separator . ' ';
                $this->arguments[] = $value->lowerValue;
                $this->arguments[] = $value->upperValue;
            }
            else {
                if ($value === null && $separator === 'and') {
                    $this->query .= $this->driver->quote($field) . ' is null ' . $separator . ' ';
                }
                else {
                    $this->query .= $this->driver->quote($field) . ' = ? ' . $separator . ' ';
                    $this->arguments[] = $value;
                }
            }
        }

        $this->query = substr($this->query, 0, -2 - strlen($separator));
    }

    public function update(Table $table, array $updates, array $conditions): QueryCollection
    {
        $this->arguments = [];

        $this->query = 'update ' . $table->name . ' set ';
        $this->appendFields($updates);

        $this->query .= ' where ';
        $this->appendConditions($conditions);

        return QueryCollection::fromQuery(new Query($this->query, $this->arguments, $this->database, Priority::UpdateRecord));
    }

    private function appendFields(array $fields): void
    {
        foreach ($fields as $field => $value) {
            $this->query .= $this->driver->quote($field) . ' = ?, ';
            $this->arguments[] = $value;
        }

        $this->query = substr($this->query, 0, -2);
    }

    public function delete(Table $table, array $conditions, Priority $priority = Priority::DeleteRecord): QueryCollection
    {
        $this->arguments = [];

        $this->query = 'delete from ' . $table->name . ' where ';
        $this->appendConditions($conditions);

        return QueryCollection::fromQuery(new Query($this->query, $this->arguments, $this->database, $priority));
    }

    public function collectionUpdate(Table $table, object $entity, string $name, Collection $type, ManagedCollection $values): QueryCollection
    {
        $joinTable = $table->storage()->store(service(JoinTableManager::class)->determineName($table->name, $name));
        $queries = new QueryCollection();

        foreach ($values->getAdditions() as $value) {
            foreach ($this->create($joinTable, ['id' => $entity, 'value' => $value], Priority::UpdateCollection) as $query) {
                $queries[] = $query;
            }
        }

        foreach ($values->getDeletions() as $value) {
            foreach ($this->delete($joinTable, ['id' => $entity, 'value' => $value], Priority::UpdateCollection) as $query) {
                $queries[] = $query;
            }
        }

        return $queries;
    }

    public function showCreate(Table $table): QueryCollection
    {
        return QueryCollection::fromQuery(new Query('show create table ' . $this->driver->quote($table->name), [], $this->database));
    }

    public function dropTable(string $name): QueryCollection
    {
        return QueryCollection::fromQuery(new Query(
            query: 'drop table if exists ' . $this->driver->quote($name),
            database: $this->database,
            priority: Priority::DeleteStore
        ));
    }

    public function createStore(Blueprint $blueprint): QueryCollection
    {
        return $this->driver->createTableBuilder()->create($blueprint);
    }

    public function create(Table $table, array $values, Priority $priority = Priority::CreateRecord): QueryCollection
    {
        $arguments = [];

        $names = [];

        foreach ($values as $field => $value) {
            $names[] = $this->driver->quote($field);
            $arguments[] = $value;
        }

        $query = 'insert into ' . $this->driver->quote($table->name)
            . ' (' . implode(',', $names) . ')'
            . ' values (' . implode(',', array_fill(0, count($names), '?')) . ')';

        return QueryCollection::fromQuery(new Query($query, $arguments, $this->database, $priority));
    }

    public function fromSelector(Selector $selector, array $arguments): Action
    {
        return $this->driver->selectQueryBuilder()->build($selector, $arguments);
    }
}
