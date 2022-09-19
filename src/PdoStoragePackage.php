<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\ServiceManager\{AsSingleton, BasePackage};

class PdoStoragePackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return $this->dependenciesByClass([
        ]);
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
