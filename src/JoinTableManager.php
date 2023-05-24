<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\Drivers\Handler;
use Medas\PdoStorage\Queries\Query;
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\Priority;

#[Service]
class JoinTableManager
{
    public function determineName(string $sourceTable, string $property): string
    {
        return $sourceTable . '__' . $property;
    }

    public function createQueries(Database $database, Handler $driver, Blueprint $blueprint, Blueprint\Field $field): array
    {
        $joinTable = $this->determineName($blueprint->name(), $field->name);

        if ($database->store($joinTable)->exists()) {
            return [];
        }

        $idField = $blueprint->primaryIndex()->fields()[0];

        $joinQuery = sprintf(
            "create table %s (
                    id %s,
                    value %s
                    )",
            $driver->quote($joinTable),
            $driver->typeHandler()->getBaseDefinition($idField),
            $driver->typeHandler()->getBaseDefinition($field, useCollectionType: true),
        );

        return [new Query(
            query: $joinQuery,
            database: $database,
            priority: Priority::AddCollectionStore,
        )];
    }
}
