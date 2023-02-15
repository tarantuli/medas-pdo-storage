<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Mysql;

use Medas\PdoStorage\Drivers\Bases\BaseCreateTableBuilder;
use Medas\StorageManager\Structure\Blueprint\{Field, Index};

class CreateTableBuilder extends BaseCreateTableBuilder
{
    private ForeignKeyConstraintBuilder $foreignKeyConstraintBuilder;
    protected function initialize(): void
    {
        $this->foreignKeyConstraintBuilder = new ForeignKeyConstraintBuilder();
    }

    protected function addKeys(): void
    {
        foreach ($this->blueprint->indexes() as $index) {
            if ($index->isPrimary) {
                $this->query .= ' primary key (';
            }
            else {
                if ($index->isUnique) {
                    $this->query .= ' unique';
                }

                $this->query .= ' key ' . $this->driver->quote($this->createIndexName($index)) . ' (';
            }

            foreach ($index->fields() as $field) {
                $this->query .= $this->driver->quote($field->name) . ',';
            }

            $this->query = substr($this->query, 0, -1) . "),\n";
        }
    }

    private function createIndexName(Index $index): string
    {
        $names = array_map(fn(Field $field) => $field->name, $index->fields());

        return sha1((implode("\n", $names)));
    }

    protected function processForeignKeys(): void
    {
        foreach ($this->blueprint->foreignKeys() as $foreignKey) {
            $this->foreignKeys[] = $this->foreignKeyConstraintBuilder
                ->buildAdd($this->blueprint->name(), $this->driver, $foreignKey);
        }
    }

}
