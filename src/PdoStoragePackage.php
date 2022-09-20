<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\ServiceManager\{AsSingleton, BasePackage};
use Medas\StorageManager\StorageManagerPackage;

class PdoStoragePackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return $this->dependenciesByClass([
            StorageManagerPackage::class,
        ]);
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
