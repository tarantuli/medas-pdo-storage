<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Database;
use Medas\PdoStorage\Drivers\{Driver, Interfaces\AlterTableBuilder};
use Medas\PdoStorage\Queries\Query;
use Medas\PdoStorage\Structure\Changes;

abstract class BaseAlterTableBuilder implements AlterTableBuilder
{
    public function __construct(
        private readonly Driver   $driver,
        private readonly Database $database,
    )
    {
    }

    public function create(Changes $changes): Query
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

        return new Query($query, [], $this->database);
    }
}
