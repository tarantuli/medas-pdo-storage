<?php

declare(strict_types=1);

namespace Medas\PdoStorage\JoinTables\NamingStrategies;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\JoinTables\NamingStrategy;

#[Service]
readonly class DoubleUnderscoreConcatenation implements NamingStrategy
{
    public function determine(string $sourceTable, string $property): string
    {
        return $sourceTable . '__' . $property;
    }
}
