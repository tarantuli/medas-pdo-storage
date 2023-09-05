<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

use Medas\Core\Interfaces\Serializer;
use Medas\PdoStorage\Database;
use Medas\PdoStorage\Drivers\Interfaces\{FieldHandler,
    PdoMigrationBuilder,
    QueryBuilders,
    TableStructureFinder,
    TypeHandler};
use Medas\PdoStorage\Table;
use Medas\StorageManager\Interfaces\RecordFetchers;

interface DriverHandler
{
    public function canHandle(string $driverName): bool;

    public function priority(): int;

    public function quote(Database $database, string $identifier): string;

    public function escape(Database $database, mixed $value): string;

    public function table(Database $database, string $name): Table;

    public function tableStructureFinder(): TableStructureFinder;

    public function fieldHandler(): FieldHandler;

    public function migrationBuilder(): PdoMigrationBuilder;

    public function queryBuilders(): QueryBuilders;

    public function serializer(): Serializer;

    public function typeHandler(): TypeHandler;

    public function recordFetchers(): RecordFetchers;

    public function tableStructureString(Table $table): string|null;
}
