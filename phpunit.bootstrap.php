<?php

declare(strict_types=1);

use Medas\ConfigManager\{ConfigManager, ConfigManagerPackage};
use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\PdoMysql\PdoMysqlPackage;
use Medas\PdoStorage\{Database, PdoStoragePackage};
use Medas\RamseyUuidBridge\RamseyUuidBridgePackage;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};
use Medas\StorageManager\StorageManager;

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();

    $config->addPackages([
        PdoStoragePackage::instance(),
        PdoMysqlPackage::instance(),
        ConfigManagerPackage::instance(),
        ConfigOptionsPackage::instance(),
        RamseyUuidBridgePackage::instance(),
    ]);

    return $config;
});

service(ConfigManager::class)
    ->readEnv(__DIR__)
    ->addDirectory('tests/MockUps');

service(StorageManager::class)
    ->add(medas()->objectInstantiator()->instantiate(Database::class));
