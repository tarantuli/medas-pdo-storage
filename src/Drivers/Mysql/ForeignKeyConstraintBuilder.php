<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Mysql;

use Medas\PdoStorage\Drivers\Driver;
use Medas\PdoStorage\Drivers\Interfaces\ForeignKeyConstraintBuilder as BuilderInterface;
use Medas\StorageManager\Structure\Blueprint\ForeignKey;

class ForeignKeyConstraintBuilder implements BuilderInterface
{
    public function buildAdd(string $entityName, Driver $driver, ForeignKey $foreignKey): string
    {
        return ' add constraint ' . $driver->quote($this->createForeignKeyName($entityName, $foreignKey)) . "\n"
            . '   foreign key (' . $driver->quote($foreignKey->field) . ")\n"
            . '   references ' . $driver->quote($foreignKey->foreignEntity)
            . ' (' . $driver->quote($foreignKey->foreignField) . ")"
            . ($foreignKey->onDeleteCascade ? ' on delete cascade' : '');
    }

    private function createForeignKeyName(string $entityName, ForeignKey $foreignKey): string
    {
        return sha1($entityName
            . "\n" . $foreignKey->field
            . "\n" . $foreignKey->foreignEntity
            . "\n" . $foreignKey->foreignField);
    }

    public function buildDrop(string $entityName, Driver $driver, ForeignKey $foreignKey): string
    {
        return ' drop constraint ' . $driver->quote($this->createForeignKeyName($entityName, $foreignKey));
    }
}
