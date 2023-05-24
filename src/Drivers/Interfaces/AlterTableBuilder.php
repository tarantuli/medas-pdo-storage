<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\Queries\QueryCollection;
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\Structure\Changes\Changes;

interface AlterTableBuilder
{
    public function create(Blueprint $blueprint, Changes $changes): QueryCollection;
}
