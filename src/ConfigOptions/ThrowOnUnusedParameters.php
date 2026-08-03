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
readonly class ThrowOnUnusedParameters implements ConfigOption, Validator
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
        return 'throw-on-unused-parameters';
    }

    public function description(): string
    {
        return 'Whether to throw when a Definition declares a parameter that no '
            . 'condition references. Off by default (unused parameters are simply '
            . 'dropped); turn it on to surface Definitions with a stale or mistyped '
            . 'parameter. Parameters marked allowUnused are exempt.';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): false
    {
        return false;
    }

    public function isValid(mixed $value): bool
    {
        return is_bool($value);
    }
}
