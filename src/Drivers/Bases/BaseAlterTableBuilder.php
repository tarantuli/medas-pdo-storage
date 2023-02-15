<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Database;
use Medas\PdoStorage\Drivers\{Driver, Interfaces\AlterTableBuilder, Interfaces\ForeignKeyConstraintBuilder};
use Medas\PdoStorage\Queries\{Query, QueryCollection};
use Medas\StorageManager\Structure\Changes\Changes;
use Medas\StorageManager\UnitOfWork\Priority;

abstract class BaseAlterTableBuilder implements AlterTableBuilder
{
    private Changes $changes;

    private string|null $baseQuery;
    private string|null $dropForeignKeysQuery;
    private string|null $addForeignKeysQuery;

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
        $this->baseQuery = null;
        $this->dropForeignKeysQuery = null;
        $this->addForeignKeysQuery = null;

        $this->processFields();
        $this->processIndexes();
        $this->processForeignKeys();

        $queryCollection = new QueryCollection([]);

        if ($this->baseQuery !== null) {
            $queryCollection[] = new Query(
                query: substr($this->baseQuery, 0, -2),
                database: $this->database,
                priority: Priority::AlterStore
            );
        }

        if ($this->dropForeignKeysQuery !== null) {
            $queryCollection[] = new Query(
                query: substr($this->dropForeignKeysQuery, 0, -2),
                database: $this->database,
                priority: Priority::DeleteStoreRelations
            );
        }

        if ($this->addForeignKeysQuery !== null) {
            $queryCollection[] = new Query(
                query: substr($this->addForeignKeysQuery, 0, -2),
                database: $this->database,
                priority: Priority::AddStoreRelations
            );
        }

        return $queryCollection;
    }

    private function processFields(): void
    {
        if ($this->changes->addFields || $this->changes->changeFields) {
            $this->baseQuery = $this->startAlterQuery();
        }

        foreach ($this->changes->addFields as $field) {
            $this->baseQuery .= sprintf(
                "add column %s %s,\n",
                $this->driver->quote($field->name),
                $this->driver->fieldHandler()->buildDefinition($field),
            );
        }

        foreach ($this->changes->changeFields as $field) {
            $this->baseQuery .= sprintf(
                "modify column %1\$s %2\$s,\n",
                $this->driver->quote($field->name),
                $this->driver->fieldHandler()->buildDefinition($field),
            );
        }
    }

    private function startAlterQuery(): string
    {
        return 'alter table ' . $this->driver->quote($this->changes->name) . "\n";
    }

    private function processIndexes(): void
    {
        // TODO need to be implemented
    }

    private function processForeignKeys(): void
    {
        if (!$this->changes->changeForeignKey && !$this->changes->addForeignKey) {
            return;
        }

        if ($this->changes->changeForeignKey) {
            $this->dropForeignKeysQuery = $this->startAlterQuery();

            foreach ($this->changes->changeForeignKey as $foreignKey) {
                $this->dropForeignKeysQuery .= $this->foreignKeyConstraintBuilder()
                        ->buildDrop($this->changes->name, $this->driver, $foreignKey) . ",\n";
            }
        }

        $this->addForeignKeysQuery = $this->startAlterQuery();
        $foreignKeys = array_merge($this->changes->changeForeignKey, $this->changes->addForeignKey);

        foreach ($foreignKeys as $foreignKey) {
            $this->addForeignKeysQuery .= $this->foreignKeyConstraintBuilder()
                    ->buildAdd($this->changes->name, $this->driver, $foreignKey) . ",\n";
        }
    }

    abstract public function foreignKeyConstraintBuilder(): ForeignKeyConstraintBuilder;
}
