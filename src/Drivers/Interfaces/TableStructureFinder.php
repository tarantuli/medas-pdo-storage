<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\Table;
use Medas\StorageManager\Structure\Blueprint;

interface TableStructureFinder
{
    public function find(Table $table): Blueprint|null;
}
