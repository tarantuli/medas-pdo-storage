<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Mysql;

use Medas\PdoStorage\Drivers\Bases\BaseCreateTableBuilder;
use Medas\StorageManager\Structure\Blueprint\{Field, ForeignKey, Index};

class CreateTableBuilder extends BaseCreateTableBuilder
{
    protected function initialize(): void
    {
        // Nothing to do
    }

    protected function addKeys(): void
    {
        foreach ($this->blueprint->indexes() as $index) {
            if ($index->isPrimary) {
                $this->query .= ' PRIMARY KEY (';
            }
            else {
                if ($index->isUnique) {
                    $this->query .= ' UNIQUE';
                }

                $this->query .= ' KEY ' . $this->driver->quote($this->createIndexName($index)) . ' (';
            }

            foreach ($index->fields as $field) {
                $this->query .= $this->driver->quote($field->name) . ',';
            }

            $this->query = substr($this->query, 0, -1) . "),\n";
        }
    }

    private function createIndexName(Index $index): string
    {
        $names = array_map(fn(Field $field) => $field->name, $index->fields);

        return sha1((implode("\n", $names)));
    }

    protected function processForeignKeys(): void
    {
        foreach ($this->blueprint->foreignKeys() as $foreignKey) {
            $this->foreignKeys[] = ' ADD CONSTRAINT ' . $this->driver->quote($this->createForeignKeyName($foreignKey)) . "\n"
                . '   FOREIGN KEY (' . $this->driver->quote($foreignKey->field) . ")\n"
                . '   REFERENCES ' . $this->driver->quote($foreignKey->foreignEntity)
                . ' (' . $this->driver->quote($foreignKey->foreignField) . ")";
        }
    }

    private function createForeignKeyName(ForeignKey $foreignKey): string
    {
        return sha1($this->blueprint->name()
            . "\n" . $foreignKey->field
            . "\n" . $foreignKey->foreignEntity
            . "\n" . $foreignKey->foreignField);
    }
}
