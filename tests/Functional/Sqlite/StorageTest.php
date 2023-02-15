<?php

declare(strict_types=1);

namespace Medas\PdoStorageTest\Functional\Sqlite;

use Medas\PdoStorage\Database;
use Medas\StorageManager\StorageManager;
use Medas\StorageManagerTest\Functional\StorageTests\AbstractStorageTestClass;

class StorageTest extends AbstractStorageTestClass
{
    protected function initialize(): void
    {
        service(StorageManager::class)->add(
            new Database('sqlite::memory:', '', '')
        );
    }
}
