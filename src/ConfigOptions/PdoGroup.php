<?php

declare(strict_types=1);

namespace Medas\PdoStorage\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup};
use Medas\StorageManager\ConfigOptions\RootGroup;

#[Service]
class PdoGroup implements ConfigGroup
{
    public function parent(): ConfigGroup|null
    {
        return RootGroup::instance();
    }

    public function name(): string
    {
        return 'pdo';
    }
}
