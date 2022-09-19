<?php

declare(strict_types=1);

use Medas\PdoStorage\PdoStoragePackage;
use Medas\ServiceManager\ServiceManager;

chdir(__DIR__);

ServiceManager::get()
    ->addPackage(PdoStoragePackage::instance());
