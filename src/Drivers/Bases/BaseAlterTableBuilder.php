<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Database;
use Medas\PdoStorage\Drivers\{Driver, Interfaces\AlterTableBuilder};
use Medas\PdoStorage\Queries\{Query, QueryCollection};
use Medas\PdoStorage\Structure\Changes;
use Medas\StorageManager\UnitOfWork\Priority;

abstract class BaseAlterTableBuilder implements AlterTableBuilder
{
    public function __construct(
        private readonly Driver   $driver,
        private readonly Database $database,
    )
    {
    }

    public function create(Changes $changes): QueryCollection
    {
        $query = 'ALTER TABLE ' . $this->driver->quote($changes->name) . "\n";

        foreach ($changes->addFields as $field) {
            $query .= sprintf(
                "ADD COLUMN %s %s,\n",
                $this->driver->quote($field->name),
                $this->driver->fieldHandler()->buildDefinition($field),
            );
        }

        foreach ($changes->changeFields as $field) {
            $query .= sprintf(
                "MODIFY COLUMN %1\$s %2\$s,\n",
                $this->driver->quote($field->name),
                $this->driver->fieldHandler()->buildDefinition($field),
            );
        }

        $query = substr($query, 0, -2);

        return new QueryCollection([new Query(
            query: $query,
            database: $this->database,
            priority: Priority::AlterStore
        )]);
    }
}
