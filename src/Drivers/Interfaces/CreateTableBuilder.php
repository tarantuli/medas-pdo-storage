<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\Queries\Query;
use Medas\StorageManager\Structure\Blueprint;

interface CreateTableBuilder
{
    public function create(Blueprint $blueprint): Query;
}
