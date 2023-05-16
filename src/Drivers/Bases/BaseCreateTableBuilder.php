<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Database;
use Medas\PdoStorage\Drivers\{Handler, Interfaces\CreateTableBuilder};
use Medas\PdoStorage\JoinTableManager;
use Medas\PdoStorage\Queries\{Query, QueryCollection};
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\Priority;

abstract class BaseCreateTableBuilder implements CreateTableBuilder
{
    protected Blueprint $blueprint;
    protected string $query;
    protected array $foreignKeys;
    /** @var Blueprint\Field[] */
    protected array $collections;

    public function __construct(
        protected readonly Handler  $driver,
        protected readonly Database $database,
    )
    {
        $this->initialize();
    }

    abstract protected function initialize(): void;

    public function create(Blueprint $blueprint): QueryCollection
    {
        $this->foreignKeys = [];
        $this->collections = [];
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

        if ($this->collections) {
            foreach ($this->collections as $collectionField) {
                $joinTable = service(JoinTableManager::class)->determineName($blueprint->name(), $collectionField->name);
                $idField = $this->blueprint->primaryIndex()->fields()[0];

                $joinQuery = sprintf(
                    "create table %s (
                    id %s,
                    value %s
                    )",
                    $this->driver->quote($joinTable),
                    $this->driver->typeHandler()->getBaseDefinition($idField),
                    $this->driver->typeHandler()->getBaseDefinition($collectionField, useCollectionType: true),
                );

                $queryCollection[] = new Query(
                    query: $joinQuery,
                    database: $this->database,
                    priority: Priority::AddCollectionStore,
                );
            }
        }

        return $queryCollection;
    }

    protected function addFields(): void
    {
        foreach ($this->blueprint->fields() as $field) {
            if ($field->type === Blueprint\Type::Collection) {
                $this->collections[] = $field;
                continue;
            }

            $definition = $this->driver->fieldHandler()->buildDefinition($field);

            if ($definition !== null) {
                $this->query .= sprintf(
                    " %s %s,\n",
                    $this->driver->quote($field->name),
                    $definition,
                );
            }
        }
    }

    abstract protected function addKeys(): void;

    abstract protected function processForeignKeys(): void;
}
