<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Sqlite;

use Medas\PdoStorage\Drivers\Bases\BaseCreateTableBuilder;
use Medas\StorageManager\Structure\Blueprint\ForeignKey;

class CreateTableBuilder extends BaseCreateTableBuilder
{
    protected function initialize(): void
    {
        // Nothing to do
    }

    protected function addKeys(): void
    {
        foreach ($this->blueprint->indexes() as $index) {
            if ($index->isPrimary || !$index->isUnique) {
                continue;
            }

            $this->query .= ' UNIQUE (';

            foreach ($index->fields as $field) {
                $this->query .= $this->driver->quote($field->name) . ',';
            }

            $this->query = substr($this->query, 0, -1) . "),\n";
        }
    }

    protected function processForeignKeys(): void
    {
        foreach ($this->blueprint->foreignKeys() as $foreignKey) {
            $this->query .= ' CONSTRAINT ' . $this->driver->quote($this->createForeignKeyName($foreignKey)) . "\n"
                . '   FOREIGN KEY (' . $this->driver->quote($foreignKey->field) . ")\n"
                . '   REFERENCES ' . $this->driver->quote($foreignKey->foreignEntity)
                . ' (' . $this->driver->quote($foreignKey->foreignField) . "),\n";
        }
    }

    private function createForeignKeyName(ForeignKey $foreignKey): string
    {
        return sha1($foreignKey->field
            . "\n" . $foreignKey->foreignEntity
            . "\n" . $foreignKey->foreignField);
    }
}
