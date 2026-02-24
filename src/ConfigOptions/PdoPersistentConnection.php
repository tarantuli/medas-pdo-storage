<?php

declare(strict_types=1);

namespace Medas\PdoStorage\ConfigOptions;

use Medas\Core\{
    Attributes\Service,
    Interfaces\ConfigGroup,
    Interfaces\ConfigOption,
    Interfaces\Validator
};

#[Service]
readonly class PdoPersistentConnection implements ConfigOption, Validator
{
    public function __construct(
        private PdoGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'persistent-connection';
    }

    public function description(): string
    {
        return 'Whether to use persistent connections';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): true
    {
        return true;
    }

    public function isValid(mixed $value): bool
    {
        return is_bool($value);
    }
}
