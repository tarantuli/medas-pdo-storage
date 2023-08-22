<?php

declare(strict_types=1);

namespace Medas\PdoStorage\ConfigOptions;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};

#[Service]
readonly class PdoName implements ConfigOption
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
        return 'name';
    }

    public function description(): string
    {
        return 'The name of this connection';
    }

    public function isValid(mixed $value): bool
    {
        return is_string($value);
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'default';
    }
}
