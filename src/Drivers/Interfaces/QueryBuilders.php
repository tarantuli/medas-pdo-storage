<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\StorageManager\Interfaces\ActionBuilders;

interface QueryBuilders extends ActionBuilders
{
    public function showTables(): ShowTablesBuilder;
}
