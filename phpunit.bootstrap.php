<?php

declare(strict_types=1);

use Medas\ConfigManager\{ConfigManager, ConfigManagerPackage};
use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\PdoStorage\Database;
use Medas\PdoStorage\PdoStoragePackage;
use Medas\ServiceManager\ServiceManager;
use Medas\StorageManager\StorageManager;

chdir(__DIR__);

ServiceManager::get()
    ->addPackage(PdoStoragePackage::instance())
    ->addPackage(ConfigManagerPackage::instance())
    ->addPackage(ConfigOptionsPackage::instance());

service(ConfigManager::class)
    ->readEnv(__DIR__)
    ->addDirectory('tests/MockUps');

service(StorageManager::class)->add(
    sm()->instantiate(Database::class)
);
