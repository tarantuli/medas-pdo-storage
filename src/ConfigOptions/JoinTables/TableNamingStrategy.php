<?php

declare(strict_types=1);

namespace Medas\PdoStorage\ConfigOptions\JoinTables;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption, Interfaces\Validator};
use Medas\PdoStorage\JoinTables\{NamingStrategies\DoubleUnderscoreConcatenation, NamingStrategy};

#[Service]
readonly class TableNamingStrategy implements ConfigOption, Validator
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

    public function isValid(mixed $value): bool
    {
        return $value instanceof NamingStrategy;
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
