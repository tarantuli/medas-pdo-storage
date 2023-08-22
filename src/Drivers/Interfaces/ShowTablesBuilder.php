<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\Queries\QueryCollection;

interface ShowTablesBuilder
{
    public function build(string $name): QueryCollection;
}
