<?php

declare(strict_types=1);

namespace Medas\PdoStorageTest\MockUps;

use Medas\Core\Interfaces\Serializer;
use Medas\PdoStorage\{Database, Drivers\DriverHandler, Drivers\Interfaces, Table};
use Medas\StorageManager\Interfaces\RecordFetchers;

class MockupDriverHandler implements DriverHandler
{
    public function priority(): int
    {
    }

    public function canHandle(string $driverName): bool
    {
    }

    public function quote(Database $database, string $identifier): string
    {
        return '`' . $identifier . '`';
    }

    public function escape(Database $database, mixed $value): string
    {
        return '"' . $value . '"';
    }

    public function table(Database $database, string $name): Table
    {
    }

    public function queryBuilders(): Interfaces\QueryBuilders
    {
    }

    public function serializer(): Serializer
    {
    }

    public function recordFetchers(): RecordFetchers
    {
    }

    public function exceptionTypeFinder(): Interfaces\ExceptionTypeFinder
    {
    }
}
