<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\Drivers\DriverHandler;
use Medas\StorageManager\Structure\Blueprint\ForeignKey;

interface ForeignKeyConstraintBuilder
{
    public function buildAdd(string $entityName, DriverHandler $driver, ForeignKey $foreignKey): string;

    public function buildDrop(string $entityName, DriverHandler $driver, ForeignKey $foreignKey): string;
}
