<?php

declare(strict_types=1);

namespace Medas\PdoStorage\ConfigOptions\JoinTables;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\ConfigGroup;
use Medas\Core\Interfaces\ConfigOption;
use Medas\PdoStorage\JoinTables\NamingStrategies\DoubleUnderscoreConcatenation;
use Medas\PdoStorage\JoinTables\NamingStrategy;

#[Service]
readonly class TableNamingStrategy implements ConfigOption
{
    public function __construct(
        private JoinTableGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'naming-strategy';
    }

    public function description(): string
    {
        return 'The strategy to use to determine the name of joining tables per property';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): NamingStrategy
    {
        return service(DoubleUnderscoreConcatenation::class);
    }
}
