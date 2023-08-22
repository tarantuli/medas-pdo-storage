<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\AsSingleton;
use Medas\ServiceManager\BasePackage;
use Medas\ServiceManager\ServiceConfig;
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\StorageManagerPackage;

class PdoStoragePackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [
            StorageManagerPackage::instance(),
        ];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }

    public function initialize(ServiceConfig $config): void
    {
        service(StorageManager::class)->registerController(service(PdoStorageController::class));

        parent::initialize($config);
    }
}
