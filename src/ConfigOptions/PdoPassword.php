<?php

declare(strict_types=1);

namespace Medas\PdoStorage\ConfigOptions;

use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};
use Medas\ServiceManager\AsSingleton;

class PdoPassword implements ConfigOption
{
    use AsSingleton;

    public function group(): ConfigGroup
    {
        return PdoGroup::instance();
    }

    public function name(): string
    {
        return 'password';
    }

    public function description(): string
    {
        return 'The password to use when connecting';
    }

    public function isValid(mixed $value): bool
    {
        return is_string($value);
    }

    public function hasDefault(): bool
    {
        return false;
    }

    public function default(): mixed
    {
        return null;
    }
}
