<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

use Medas\Core\Interfaces\Serializer;
use Medas\PdoStorage\Drivers\Interfaces\{AlterTableBuilder,
    CreateTableBuilder,
    FieldHandler,
    QueryBuilder,
    SelectQueryBuilder,
    TableStructureFinder,
    TypeHandler
};
use Medas\StorageManager\Migrations\MigrationBuilder;

interface Driver
{
    public function canHandle(string $driverName): bool;

    public function quote(string $identifier): string;

    public function escape(mixed $value): string;

    public function tableStructureFinder(): TableStructureFinder;

    public function alterTableBuilder(): AlterTableBuilder;

    public function createTableBuilder(): CreateTableBuilder;

    public function fieldHandler(): FieldHandler;

    public function migrationBuilder(): MigrationBuilder;

    public function queryBuilder(): QueryBuilder;

    public function selectQueryBuilder(): SelectQueryBuilder;

    public function serializer(): Serializer;

    public function typeHandler(): TypeHandler;
}
