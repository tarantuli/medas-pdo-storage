<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\{AsSingleton, BasePackage, Interfaces\ServiceConfig};
use Medas\StorageManager\{StorageManager, StorageManagerPackage};

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
        $pdoStorageController = service(PdoStorageController::class);

        service(StorageManager::class)->registerController($pdoStorageController);

        // Roll back any open transaction on shutdown to prevent lock leaks
        // on persistent connections and in error scenarios.
        register_shutdown_function(function () use ($pdoStorageController) {
            try {
                $pdoStorageController
                    ->transaction()
                    ->rollback();
            }
            catch (\Throwable) {
                // Silently discard — shutdown functions must not throw
            }
        });

        parent::initialize($config);
    }
}
