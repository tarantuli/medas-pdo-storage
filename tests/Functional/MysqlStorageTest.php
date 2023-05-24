<?php

declare(strict_types=1);

namespace Medas\PdoStorageTest\Functional;

use Medas\PdoStorage\Database;
use Medas\StorageManager\StorageManager;
use Medas\StorageManagerTest\Functional\StorageTests\AbstractStorageTestClass;

class MysqlStorageTest extends AbstractStorageTestClass
{
    protected function initialize(): void
    {
        service(StorageManager::class)->add(
            medas()->objectInstantiator()->instantiate(Database::class)
        );
    }

    protected function migrationAssertions(string $migration): void
    {
        self::assertStringContainsString('alter table', $migration);
    }
}
