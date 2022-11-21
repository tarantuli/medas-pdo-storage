<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\{Queries\Query, Structure\Changes};

interface AlterTableBuilder
{
    public function create(Changes $changes): Query;
}
