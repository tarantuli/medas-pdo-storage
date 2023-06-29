<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Drivers\{Interfaces\CreateTableBuilder};
use Medas\PdoStorage\Queries\{Query, QueryCollection};
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\Priority;

abstract class BaseCreateTableBuilder extends BaseBuilder implements CreateTableBuilder
{
    public function create(Blueprint $blueprint): QueryCollection
    {
        $job = new BuildJob($blueprint);

        $tableName = $this->driver->quote($job->blueprint->name());

        $job->baseQuery = sprintf(/** @lang text */ "create table %s (\n", $tableName);

        $this->addFields($job);
        $this->addKeys($job);
        $this->processForeignKeys($job);

        $job->baseQuery = substr($job->baseQuery, 0, -2);
        $job->baseQuery .= "\n)\n";

        $job->queryCollection[] = new Query(
            query: $job->baseQuery,
            database: $this->database,
            priority: Priority::CreateStore
        );

        if ($job->foreignKeys) {
            $query = sprintf(
                "alter table %s\n%s",
                $tableName,
                implode(",\n", $job->foreignKeys)
            );

            $job->queryCollection[] = new Query(
                query: $query,
                database: $this->database,
                priority: Priority::AddStoreRelations
            );
        }

        $this->processCollections($job);

        return $job->queryCollection;
    }

    protected function addFields(BuildJob $job): void
    {
        foreach ($job->blueprint->fields() as $field) {
            if ($field->store !== null && $field->store !== $job->blueprint->name()) {
                // If this is the primary key, add it without generating value
                $primaryIndex = $job->blueprint->primaryIndex();
                if ($primaryIndex && in_array($field, $primaryIndex->fields())) {
                    $field->isGenerated = false;
                }
                else {
                    continue;
                }
            }

            if ($field->type === Blueprint\Type::Collection) {
                $job->collections[] = $field;
                continue;
            }

            $definition = $this->driver->fieldHandler()->buildDefinition($field);

            if ($definition !== null) {
                $job->baseQuery .= sprintf(
                    " %s %s,\n",
                    $this->driver->quote($field->name),
                    $definition,
                );
            }
        }
    }

    abstract protected function addKeys(BuildJob $job): void;

    abstract protected function processForeignKeys(BuildJob $job): void;
}
