<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

use Medas\Core\Interfaces\Serializer;
use Medas\PdoStorage\{Database, Table};
use Medas\StorageManager\Interfaces\RecordFetchers;

interface DriverHandler
{
    public function canHandle(string $driverName): bool;

    public function priority(): int;

    public function quote(Database $database, string $identifier): string;

    public function escape(Database $database, mixed $value): string;

    public function table(Database $database, string $name): Table;

    public function queryBuilders(): Interfaces\QueryBuilders;

    public function serializer(): Serializer;

    public function recordFetchers(): RecordFetchers;

    public function tableStructureString(Table $table): string|null;

    public function exceptionTypeFinder(): Interfaces\ExceptionTypeFinder;
}
