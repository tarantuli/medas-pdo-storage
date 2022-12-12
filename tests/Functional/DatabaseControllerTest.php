<?php

declare(strict_types=1);

namespace Medas\PdoStorageTest\Functional;

use Medas\PdoStorageTest\MockUps\Enums\{IntBackedEnum, StringBackedEnum};
use PHPUnit\Framework\TestCase;

class DatabaseControllerTest extends TestCase
{
    public function testBackedEnums(): void
    {
        self::assertEquals('\'1\'', storage()->controller()->escapeValue(IntBackedEnum::Value1));
        self::assertEquals('\'one\'', storage()->controller()->escapeValue(StringBackedEnum::Value1));
    }

    public function testBooleans(): void
    {
        self::assertEquals('\'1\'', storage()->controller()->escapeValue(true));
        self::assertEquals('\'0\'', storage()->controller()->escapeValue(false));
    }
}
