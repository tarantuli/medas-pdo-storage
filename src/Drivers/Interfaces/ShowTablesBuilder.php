<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\{Database, Queries\QuerySet};

interface ShowTablesBuilder
{
    public function build(Database $database, string $name): QuerySet;
}
