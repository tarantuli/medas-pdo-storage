<?php

declare(strict_types=1);

namespace Medas\PdoStorage\JoinTables\NamingStrategies;

use Medas\PdoStorage\JoinTables\NamingStrategy;

class DoubleUnderscoreConcatenation implements NamingStrategy
{
    public function determine(string $sourceTable, string $property): string
    {
        return $sourceTable . '__' . $property;
    }
}
