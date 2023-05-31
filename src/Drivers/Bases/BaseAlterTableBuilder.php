<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Drivers\{Interfaces\AlterTableBuilder, Interfaces\ForeignKeyConstraintBuilder};
use Medas\PdoStorage\Queries\{Query, QueryCollection};
use Medas\StorageManager\Structure\{Blueprint, Blueprint\Type, Changes\Changes};
use Medas\StorageManager\UnitOfWork\Priority;

abstract class BaseAlterTableBuilder extends BaseBuilder implements AlterTableBuilder
{
    public function create(Blueprint $blueprint, Changes $changes): QueryCollection
    {
        $job = new BuildJob($blueprint);

        $job->changes = $changes;

        $this->processFields($job);
        $this->processIndexes($job);
        $this->processForeignKeys($job);

        if ($job->baseQuery !== null) {
            $job->queryCollection[] = new Query(
                query: substr($job->baseQuery, 0, -2),
                database: $this->database,
                priority: Priority::AlterStore
            );
        }

        if ($job->dropForeignKeysQuery !== null) {
            $job->queryCollection[] = new Query(
                query: substr($job->dropForeignKeysQuery, 0, -2),
                database: $this->database,
                priority: Priority::DeleteStoreRelations
            );
        }

        if ($job->addForeignKeysQuery !== null) {
            $job->queryCollection[] = new Query(
                query: substr($job->addForeignKeysQuery, 0, -2),
                database: $this->database,
                priority: Priority::AddStoreRelations
            );
        }

        $this->processCollections($job);

        return $job->queryCollection;
    }

    private function processFields(BuildJob $job): void
    {
        foreach ($job->changes->addFields as $field) {
            if ($field->type === Type::Collection) {
                $job->collections[] = $field;
                continue;
            }

            $definition = $this->driver->fieldHandler()->buildDefinition($field);

            if ($definition !== null) {
                if ($job->baseQuery === null) {
                    $job->baseQuery = $this->startAlterQuery($job);
                }
                $job->baseQuery .= sprintf(
                    "add column %s %s,\n",
                    $this->driver->quote($field->name),
                    $definition,
                );
            }
        }

        foreach ($job->changes->changeFields as $field) {
            $definition = $this->driver->fieldHandler()->buildDefinition($field);

            if ($definition !== null) {
                if ($job->baseQuery === null) {
                    $job->baseQuery = $this->startAlterQuery($job);
                }
                $job->baseQuery .= sprintf(
                    "modify column %1\$s %2\$s,\n",
                    $this->driver->quote($field->name),
                    $definition,
                );
            }
        }
    }

    private function startAlterQuery(BuildJob $job): string
    {
        return 'alter table ' . $this->driver->quote($job->changes->name) . "\n";
    }

    private function processIndexes(BuildJob $job): void
    {
        // TODO need to be implemented
    }

    private function processForeignKeys(BuildJob $job): void
    {
        if (!$job->changes->changeForeignKey && !$job->changes->addForeignKey) {
            return;
        }

        if ($job->changes->changeForeignKey) {
            $job->dropForeignKeysQuery = $this->startAlterQuery($job);

            foreach ($job->changes->changeForeignKey as $foreignKey) {
                $job->dropForeignKeysQuery .= $this->foreignKeyConstraintBuilder($job)
                        ->buildDrop($job->changes->name, $this->driver, $foreignKey) . ",\n";
            }
        }

        $job->addForeignKeysQuery = $this->startAlterQuery($job);
        $foreignKeys = array_merge($job->changes->changeForeignKey, $job->changes->addForeignKey);

        foreach ($foreignKeys as $foreignKey) {
            $job->addForeignKeysQuery .= $this->foreignKeyConstraintBuilder($job)
                    ->buildAdd($job->changes->name, $this->driver, $foreignKey) . ",\n";
        }
    }

    abstract public function foreignKeyConstraintBuilder(BuildJob $job): ForeignKeyConstraintBuilder;
}
