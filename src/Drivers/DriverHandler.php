<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

use Medas\Core\Interfaces\Serializer;
use Medas\PdoStorage\Drivers\Interfaces\{FieldHandler,
    QueryBuilders,
    SelectQueryBuilder,
    TableStructureFinder,
    TypeHandler};
use Medas\PdoStorage\Table;
use Medas\StorageManager\Interfaces\RecordFetchers;
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Migrations\MigrationBuilder;

interface DriverHandler
{
    public function canHandle(string $driverName): bool;

    public function priority(): int;

    public function quote(Storage $storage, string $identifier): string;

    public function escape(Storage $storage, mixed $value): string;

    public function table(Storage $storage, string $name): Table;

    public function tableStructureFinder(): TableStructureFinder;

    public function fieldHandler(): FieldHandler;

    public function migrationBuilder(): MigrationBuilder;

    public function queryBuilders(): QueryBuilders;

    public function selectQueryBuilder(): SelectQueryBuilder;

    public function serializer(): Serializer;

    public function typeHandler(): TypeHandler;

    public function recordFetchers(): RecordFetchers;
}
