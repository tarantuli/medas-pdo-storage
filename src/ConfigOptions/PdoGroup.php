<?php

declare(strict_types=1);

namespace Medas\PdoStorage\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup};
use Medas\StorageManager\ConfigOptions\StorageManagerConfigGroup;

#[Service]
readonly class PdoGroup implements ConfigGroup
{
    public function __construct(
        private StorageManagerConfigGroup $group,
    )
    {
    }

    public function parent(): ConfigGroup|null
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'pdo';
    }
}
