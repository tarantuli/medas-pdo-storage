<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\Queries\QueryCollection;
use Medas\StorageManager\Structure\Changes\Changes;

interface AlterTableBuilder
{
    public function create(Changes $changes): QueryCollection;
}
