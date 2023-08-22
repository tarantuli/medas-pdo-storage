<?php

declare(strict_types=1);

namespace Medas\PdoStorageTest\Functional;

use Medas\PdoStorageTest\MockUps\Enums\{IntBackedEnum, StringBackedEnum};
use Medas\StorageManager\StorageManager;
use PHPUnit\Framework\TestCase;

class DatabaseControllerTest extends TestCase
{
    public function testBackedEnums(): void
    {
        $controller = service(StorageManager::class)->controller();

        self::assertEquals('\'1\'', $controller->escapeValue(IntBackedEnum::Value1));
        self::assertEquals('\'one\'', $controller->escapeValue(StringBackedEnum::Value1));
    }

    public function testBooleans(): void
    {
        $controller = service(StorageManager::class)->controller();
        self::assertEquals('\'1\'', $controller->escapeValue(true));
        self::assertEquals('\'0\'', $controller->escapeValue(false));
    }
}
