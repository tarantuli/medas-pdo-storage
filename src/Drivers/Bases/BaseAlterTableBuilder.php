<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Drivers\{Interfaces\AlterTableBuilder, Interfaces\ForeignKeyConstraintBuilder};
use Medas\PdoStorage\Queries\{Query, QueryCollection};
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\Structure\Blueprint\Type;
use Medas\StorageManager\Structure\Changes\Changes;
use Medas\StorageManager\UnitOfWork\Priority;

abstract class BaseAlterTableBuilder extends BaseBuilder implements AlterTableBuilder
{
    private Changes $changes;

    private string|null $baseQuery;
    private string|null $dropForeignKeysQuery;
    private string|null $addForeignKeysQuery;

    public function create(Blueprint $blueprint, Changes $changes): QueryCollection
    {
        $this->changes = $changes;
        $this->blueprint = $blueprint;
        $this->baseQuery = null;
        $this->dropForeignKeysQuery = null;
        $this->addForeignKeysQuery = null;
        $this->collections = [];

        $this->processFields();
        $this->processIndexes();
        $this->processForeignKeys();

        $this->queryCollection = new QueryCollection();

        if ($this->baseQuery !== null) {
            $this->queryCollection[] = new Query(
                query: substr($this->baseQuery, 0, -2),
                database: $this->database,
                priority: Priority::AlterStore
            );
        }

        if ($this->dropForeignKeysQuery !== null) {
            $this->queryCollection[] = new Query(
                query: substr($this->dropForeignKeysQuery, 0, -2),
                database: $this->database,
                priority: Priority::DeleteStoreRelations
            );
        }

        if ($this->addForeignKeysQuery !== null) {
            $this->queryCollection[] = new Query(
                query: substr($this->addForeignKeysQuery, 0, -2),
                database: $this->database,
                priority: Priority::AddStoreRelations
            );
        }

        $this->processCollections();

        return $this->queryCollection;
    }

    private function processFields(): void
    {
        foreach ($this->changes->addFields as $field) {
            if ($field->type === Type::Collection) {
                $this->collections[] = $field;
                continue;
            }

            $definition = $this->driver->fieldHandler()->buildDefinition($field);

            if ($definition !== null) {
                if ($this->baseQuery === null) {
                    $this->baseQuery = $this->startAlterQuery();
                }
                $this->baseQuery .= sprintf(
                    "add column %s %s,\n",
                    $this->driver->quote($field->name),
                    $definition,
                );
            }
        }

        foreach ($this->changes->changeFields as $field) {
            $definition = $this->driver->fieldHandler()->buildDefinition($field);

            if ($definition !== null) {
                if ($this->baseQuery === null) {
                    $this->baseQuery = $this->startAlterQuery();
                }
                $this->baseQuery .= sprintf(
                    "modify column %1\$s %2\$s,\n",
                    $this->driver->quote($field->name),
                    $definition,
                );
            }
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
