<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\Queries\QueryCollection;
use Medas\StorageManager\Structure\Blueprint;

interface CreateTableBuilder
{
    public function create(Blueprint $blueprint): QueryCollection;
}
