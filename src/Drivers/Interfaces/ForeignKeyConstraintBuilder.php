<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\Drivers\Handler;
use Medas\StorageManager\Structure\Blueprint\ForeignKey;

interface ForeignKeyConstraintBuilder
{
    public function buildAdd(string $entityName, Handler $driver, ForeignKey $foreignKey): string;

    public function buildDrop(string $entityName, Handler $driver, ForeignKey $foreignKey): string;
}
