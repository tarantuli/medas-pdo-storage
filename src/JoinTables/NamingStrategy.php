<?php

declare(strict_types=1);

namespace Medas\PdoStorage\JoinTables;

interface NamingStrategy
{
    public function determine(string $sourceTable, string $property): string;
}
