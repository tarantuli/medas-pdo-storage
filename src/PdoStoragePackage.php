<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\{AsSingleton, BasePackage, Interfaces\ServiceConfigBuilder};
use Medas\Json\JsonPackage;
use Medas\StorageManager\StorageManagerPackage;

class PdoStoragePackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [
            JsonPackage::instance(),
            StorageManagerPackage::instance(),
        ];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }

    public function initialize(ServiceConfigBuilder $config): void
    {
        // Roll back any open transaction on shutdown to prevent lock leaks
        // on persistent connections and in error scenarios.
        register_shutdown_function(function () {
            try {
                service(PdoStorageController::class)
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
