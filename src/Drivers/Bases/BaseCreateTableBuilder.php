<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Database;
use Medas\PdoStorage\Drivers\{DriverHandler, Interfaces\CreateTableBuilder};
use Medas\PdoStorage\Queries\{Query, QueryCollection};
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\Priority;

abstract class BaseCreateTableBuilder implements CreateTableBuilder
{
    protected Blueprint $blueprint;
    protected string $query;
    protected array $foreignKeys;

    public function __construct(
        protected readonly DriverHandler $driver,
        protected readonly Database      $database,
    )
    {
        $this->initialize();
    }

    abstract protected function initialize(): void;

    public function create(Blueprint $blueprint): QueryCollection
    {
        $this->foreignKeys = [];
        $this->blueprint = $blueprint;

        $tableName = $this->driver->quote($this->blueprint->name());

        $this->query = sprintf(/** @lang text */ "create table %s (\n", $tableName);

        $this->addFields();
        $this->addKeys();
        $this->processForeignKeys();

        $this->query = substr($this->query, 0, -2);
        $this->query .= "\n)\n";

        $queryCollection = new QueryCollection([new Query(
            query: $this->query,
            database: $this->database,
            priority: Priority::CreateStore
        )]);

        if ($this->foreignKeys) {
            $query = sprintf(
                "alter table %s\n%s",
                $tableName,
                implode(",\n", $this->foreignKeys)
            );

            $queryCollection[] = new Query(
                query: $query,
                database: $this->database,
                priority: Priority::AddStoreRelations
            );
        }

        return $queryCollection;
    }

    protected function addFields(): void
    {
        foreach ($this->blueprint->fields() as $field) {
            $this->query .= sprintf(
                " %s %s,\n",
                $this->driver->quote($field->name),
                $this->driver->fieldHandler()->buildDefinition($field),
            );
        }
    }

    abstract protected function addKeys(): void;

    abstract protected function processForeignKeys(): void;
}
