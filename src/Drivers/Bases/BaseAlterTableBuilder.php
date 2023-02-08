<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Database;
use Medas\PdoStorage\Drivers\{Driver, Interfaces\AlterTableBuilder, Mysql\ForeignKeyConstraintBuilder};
use Medas\PdoStorage\Queries\{Query, QueryCollection};
use Medas\StorageManager\Structure\Changes\Changes;
use Medas\StorageManager\UnitOfWork\Priority;

abstract class BaseAlterTableBuilder implements AlterTableBuilder
{
    private string $baseQuery;
    private string|null $dropForeignKeysQuery = null;
    private string|null $addForeignKeysQuery = null;
    private Changes $changes;

    // TODO this is wrong, it should return an interface, instead of a MySQL specific implementation
    abstract public function foreignKeyConstraintBuilder(): ForeignKeyConstraintBuilder;

    public function __construct(
        private readonly Driver   $driver,
        private readonly Database $database,
    )
    {
        $this->initialize();
    }

    abstract protected function initialize(): void;

    public function create(Changes $changes): QueryCollection
    {
        $this->changes = $changes;
        $this->baseQuery = $this->startAlterQuery();
        $this->dropForeignKeysQuery = null;
        $this->addForeignKeysQuery = null;

        $this->processFields();
        $this->processIndexes();
        $this->processForeignKeys();

        $this->baseQuery = substr($this->baseQuery, 0, -2);

        $queryCollection = new QueryCollection([new Query(
            query: $this->baseQuery,
            database: $this->database,
            priority: Priority::AlterStore
        )]);

        if ($this->dropForeignKeysQuery !== null) {
            $queryCollection[] = new Query(
                query: $this->dropForeignKeysQuery,
                database: $this->database,
                priority: Priority::DeleteStoreRelations
            );
        }

        if ($this->addForeignKeysQuery !== null) {
            $queryCollection[] = new Query(
                query: $this->addForeignKeysQuery,
                database: $this->database,
                priority: Priority::AddStoreRelations
            );
        }

        return $queryCollection;
    }

    private function processFields(): void
    {
        foreach ($this->changes->addFields as $field) {
            $this->baseQuery .= sprintf(
                "ADD COLUMN %s %s,\n",
                $this->driver->quote($field->name),
                $this->driver->fieldHandler()->buildDefinition($field),
            );
        }

        foreach ($this->changes->changeFields as $field) {
            $this->baseQuery .= sprintf(
                "MODIFY COLUMN %1\$s %2\$s,\n",
                $this->driver->quote($field->name),
                $this->driver->fieldHandler()->buildDefinition($field),
            );
        }
    }

    private function processIndexes(): void
    {
        // TODO need to be implemented
    }

    private function processForeignKeys(): void
    {
        if (!$this->changes->changeForeignKey || $this->changes->addForeignKey) {
            return;
        }

        if ($this->changes->changeForeignKey) {
            $this->dropForeignKeysQuery = $this->startAlterQuery();

            foreach ($this->dropForeignKeysQuery as $foreignKey) {
                $this->dropForeignKeysQuery .= $this->foreignKeyConstraintBuilder()
                    ->buildDrop($this->changes->name, $this->driver, $foreignKey);
            }

            $this->addForeignKeysQuery = $this->startAlterQuery();
            $foreignKeys = array_merge($this->changes->changeForeignKey, $this->changes->addForeignKey);

            foreach ($foreignKeys as $foreignKey) {
                $this->addForeignKeysQuery .= $this->foreignKeyConstraintBuilder()
                    ->buildAdd($this->changes->name, $this->driver, $foreignKey);
            }
        }
    }

    private function startAlterQuery(): string
    {
        return 'ALTER TABLE ' . $this->driver->quote($this->changes->name) . "\n";
    }
}
