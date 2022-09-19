<?php

declare(strict_types=1);

use Medas\Placeholder\PlaceholderPackage;
use Medas\ServiceManager\ServiceManager;

chdir(__DIR__);

ServiceManager::get()
    ->addPackage(PlaceholderPackage::instance());
