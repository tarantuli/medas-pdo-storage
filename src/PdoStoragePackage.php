<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\AsSingleton;
use Medas\ServiceManager\BasePackage;
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
