<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Sqlite;

use Medas\PdoStorage\Drivers\Driver;
use Medas\PdoStorage\Drivers\Interfaces\ForeignKeyConstraintBuilder as BuilderInterface;
use Medas\StorageManager\Structure\Blueprint\ForeignKey;

class ForeignKeyConstraintBuilder implements BuilderInterface
{
    public function buildAdd(string $entityName, Driver $driver, ForeignKey $foreignKey): string
    {
        return ' ADD CONSTRAINT ' . $driver->quote($this->createForeignKeyName($entityName, $foreignKey)) . "\n"
            . '   FOREIGN KEY (' . $driver->quote($foreignKey->field) . ")\n"
            . '   REFERENCES ' . $driver->quote($foreignKey->foreignEntity)
            . ' (' . $driver->quote($foreignKey->foreignField) . ")"
            . ($foreignKey->onDeleteCascade ? ' ON DELETE CASCADE' : '');
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
        return ' DROP CONSTRAINT ' . $driver->quote($this->createForeignKeyName($entityName, $foreignKey));
    }
}
