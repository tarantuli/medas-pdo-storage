<?php

declare(strict_types=1);

namespace Medas\PdoStorageTest\Functional\MySql;

use Medas\PdoStorage\Database;
use Medas\StorageManager\StorageManager;
use Medas\StorageManagerTest\Functional\StorageTests\AbstractStorageTest;

class StorageTest extends AbstractStorageTest
{
    protected function initialize(): void
    {
        service(StorageManager::class)->add(
            sm()->instantiate(Database::class)
        );
    }
}
