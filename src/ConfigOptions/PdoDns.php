<?php

declare(strict_types=1);

namespace Medas\PdoStorage\ConfigOptions;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{ConfigGroup, ConfigOption, Validator};

#[Service]
readonly class PdoDns implements ConfigOption, Validator
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
        return 'dns';
    }

    public function description(): string
    {
        return 'The complete dns string to the database';
    }

    public function isValid(mixed $value): bool
    {
        return is_string($value);
    }

    public function hasDefault(): bool
    {
        return false;
    }

    public function default(): null
    {
        return null;
    }
}
