<?php

declare(strict_types=1);

namespace Medas\PdoStorage\ConfigOptions;

use Medas\ServiceManager\AsSingleton;
use Medas\Core\Interfaces\ConfigGroup;

class RootGroup implements ConfigGroup
{
    use AsSingleton;

    public function parent(): ConfigGroup|null
    {
        return null;
    }

    public function name(): string
    {
        return 'storage';
    }
}
