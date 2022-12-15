<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\EntityManager\Filters\{Between, LessThan, MoreThan};
use Medas\EntityManager\Selector\Selector;
use Medas\PdoStorage\Database;
use Medas\PdoStorage\Drivers\{Driver, Interfaces\QueryBuilder};
use Medas\PdoStorage\Queries\{Query, QueryCollection};
use Medas\PdoStorage\Table;
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\{Action, Priority};

abstract class BaseQueryBuilder implements QueryBuilder
{
    protected string $query;
    protected array $arguments;

    public function __construct(
        protected readonly Driver   $driver,
        protected readonly Database $database,
    )
    {
    }

    /** @param Table[] $tables */
    public function select(array $tables, array $filters): Query
    {
        $this->arguments = [];
        $this->query = /** @lang text */
            'SELECT * FROM ';

        foreach ($tables as $table) {
            $this->query .= $this->driver->quote($table->name) . ',';
        }

        $this->query = substr($this->query, 0, -1);

        if ($filters) {
            $this->query .= ' WHERE ';
            $this->appendConditions($filters);
        }

        return new Query($this->query, $this->arguments, $this->database);
    }

    /** @noinspection PhpSameParameterValueInspection */
    private function appendConditions(array $filters, string $separator = 'AND'): void
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
                $this->query .= $this->driver->quote($value->field) . 'BETWEEN ? AND ? ' . $separator . ' ';
                $this->arguments[] = $value->lowerValue;
                $this->arguments[] = $value->upperValue;
            }
            else {
                if ($value === null && $separator === 'AND') {
                    $this->query .= $this->driver->quote($field) . ' IS NULL ' . $separator . ' ';
                }
                else {
                    $this->query .= $this->driver->quote($field) . ' = ? ' . $separator . ' ';
                    $this->arguments[] = $value;
                }
            }
        }

        $this->query = substr($this->query, 0, -2 - strlen($separator));
    }

    public function update(Table $table, array $updates, array $conditions): Query
    {
        $this->arguments = [];

        $this->query = 'UPDATE ' . $table->name . ' SET ';
        $this->appendFields($updates);

        $this->query .= ' WHERE ';
        $this->appendConditions($conditions);

        return new Query($this->query, $this->arguments, $this->database, Priority::UpdateRecord);
    }

    private function appendFields(array $fields): void
    {
        foreach ($fields as $field => $value) {
            $this->query .= $this->driver->quote($field) . ' = ?, ';
            $this->arguments[] = $value;
        }

        $this->query = substr($this->query, 0, -2);
    }

    public function delete(Table $table, array $conditions): Query
    {
        $this->arguments = [];

        $this->query = 'DELETE FROM ' . $table->name . ' WHERE ';
        $this->appendConditions($conditions);

        return new Query($this->query, $this->arguments, $this->database, Priority::DeleteRecord);
    }

    public function showCreate(Table $table): Query
    {
        return new Query('SHOW CREATE TABLE ' . $this->driver->quote($table->name), [], $this->database);
    }

    public function dropTable(string $name): Query
    {
        return new Query(
            query: 'DROP TABLE IF EXISTS ' . $this->driver->quote($name),
            database: $this->database,
            priority: Priority::DeleteStore
        );
    }

    public function createStore(Blueprint $blueprint): QueryCollection
    {
        return $this->driver->createTableBuilder()->create($blueprint);
    }

    public function create(Table $table, array $values): Query
    {
        $this->arguments = [];

        $names = [];

        foreach ($values as $field => $value) {
            $names[] = $this->driver->quote($field);
            $this->arguments[] = $value;
        }

        $this->query = 'INSERT INTO ' . $this->driver->quote($table->name)
            . ' (' . implode(',', $names) . ')'
            . ' VALUES (' . implode(',', array_fill(0, count($names), '?')) . ')';

        return new Query($this->query, $this->arguments, $this->database, Priority::CreateRecord);
    }

    public function fromSelector(Selector $selector, array $arguments): Action
    {
        return $this->driver->selectQueryBuilder()->build($selector, $arguments);
    }
}
