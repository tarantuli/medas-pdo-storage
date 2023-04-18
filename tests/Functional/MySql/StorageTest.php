<?php

declare(strict_types=1);

namespace Medas\PdoStorageTest\Functional\MySql;

use Medas\Core\GlobalRepository;
use Medas\PdoStorage\Database;
use Medas\StorageManager\StorageManager;
use Medas\StorageManagerTest\Functional\StorageTests\AbstractStorageTestClass;

class StorageTest extends AbstractStorageTestClass
{
    protected function initialize(): void
    {
        service(StorageManager::class)->add(
            GlobalRepository::objectInstantiator()->instantiate(Database::class)
        );
    }

    protected function migrationAssertions(string $migration): void
    {
        self::assertStringContainsString('alter table', $migration);
    }
}
