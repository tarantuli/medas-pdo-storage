<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Database;
use Medas\PdoStorage\Drivers\{Driver, Interfaces\CreateTableBuilder};
use Medas\PdoStorage\Queries\Query;
use Medas\StorageManager\Structure\Blueprint;

abstract class BaseCreateTableBuilder implements CreateTableBuilder
{
    protected Blueprint $blueprint;
    protected string $query;

    public function __construct(
        protected readonly Driver   $driver,
        protected readonly Database $database,
    )
    {
        $this->initialize();
    }

    abstract protected function initialize(): void;

    public function create(Blueprint $blueprint): Query
    {
        $this->blueprint = $blueprint;

        $this->query = sprintf(/** @lang text */ "CREATE TABLE %s (\n", $this->driver->quote($this->blueprint->name));

        $this->addFields();
        $this->addKeys();
        $this->addForeignKeys();

        $this->query = substr($this->query, 0, -2);
        $this->query .= "\n)\n";

        return new Query($this->query, [], $this->database);
    }

    protected function addFields(): void
    {
        foreach ($this->blueprint->fields as $field) {
            $this->query .= sprintf(
                " %s %s,\n",
                $this->driver->quote($field->name),
                $this->driver->fieldHandler()->buildDefinition($field),
            );
        }
    }

    abstract protected function addKeys(): void;

    abstract protected function addForeignKeys(): void;
}
