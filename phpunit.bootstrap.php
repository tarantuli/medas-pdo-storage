<?php

declare(strict_types=1);

use Medas\ConfigManager\{ConfigManager, ConfigManagerPackage};
use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\ObjectInstantiator\ObjectInstantiator;
use Medas\PdoStorage\{Database, PdoStoragePackage};
use Medas\PdoStorageTest\MockUps\TestPackage;
use Medas\RamseyUuidBridge\RamseyUuidBridgePackage;
use Medas\ServiceManager\{ServiceConfigBuilder, ServiceManager};
use Medas\StorageManager\StorageManager;

chdir(__DIR__);

new ServiceManager(function (): ServiceConfigBuilder {
    $config = new ServiceConfigBuilder(ObjectInstantiator::class);

    $config->addPackages([
        PdoStoragePackage::instance(),
        ConfigManagerPackage::instance(),
        ConfigOptionsPackage::instance(),
        RamseyUuidBridgePackage::instance(),
        TestPackage::instance(),
    ]);

    return $config;
});

service(ConfigManager::class)
    ->readEnv(__DIR__)
    ->addDirectory('tests/MockUps');

service(StorageManager::class)
    ->add(medas()->objectInstantiator()->instantiate(Database::class));
