<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

use Medas\Core\Interfaces\Serializer;
use Medas\PdoStorage\{Database, Exceptions\ExceptionType, Table};
use Medas\StorageManager\{Interfaces\RecordFetchers, Migrations\MigrationBuilder};

interface DriverHandler
{
    public function canHandle(string $driverName): bool;

    public function priority(): int;

    public function quote(Database $database, string $identifier): string;

    public function escape(Database $database, mixed $value): string;

    public function table(Database $database, string $name): Table;

    public function tableStructureFinder(): Interfaces\TableStructureFinder;

    public function fieldHandler(): Interfaces\FieldHandler;

    public function migrationBuilder(): MigrationBuilder;

    public function queryBuilders(): Interfaces\QueryBuilders;

    public function serializer(): Serializer;

    public function typeHandler(): Interfaces\TypeHandler;

    public function recordFetchers(): RecordFetchers;

    public function tableStructureString(Table $table): string|null;

    public function exceptionTypeFinder(\Exception|\Error $e): ExceptionType;
}
