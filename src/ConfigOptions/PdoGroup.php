<?php

declare(strict_types=1);

namespace Medas\PdoStorage\ConfigOptions;

use Medas\Core\{AsSingleton, Interfaces\ConfigGroup};
use Medas\StorageManager\ConfigOptions\RootGroup;

class PdoGroup implements ConfigGroup
{
    use AsSingleton;

    public function parent(): ConfigGroup|null
    {
        return RootGroup::instance();
    }

    public function name(): string
    {
        return 'pdo';
    }
}
