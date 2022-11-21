<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

use Medas\PdoStorage\DatabaseController;
use Medas\PdoStorage\Drivers\Interfaces\{AlterTableBuilder,
    CreateTableBuilder,
    FieldHandler,
    QueryBuilder,
    SelectQueryBuilder,
    TypeHandler
};
use Medas\StorageManager\Entities\TypeSerializer;
use Medas\StorageManager\Migrations\MigrationBuilder;

abstract class BaseDriver implements Driver
{
    protected AlterTableBuilder $alterTableBuilder;
    protected CreateTableBuilder $createTableBuilder;
    protected FieldHandler $fieldHandler;
    protected MigrationBuilder $migrationBuilder;
    protected QueryBuilder $queryBuilder;
    protected SelectQueryBuilder $selectQueryBuilder;
    protected TypeHandler $typeHandler;
    protected TypeSerializer $serializer;

    public function __construct(
        protected readonly DatabaseController $controller,
    )
    {
        $this->initialize();
    }

    abstract protected function initialize(): void;

    public function queryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function serializer(): TypeSerializer
    {
        return $this->serializer;
    }

    public function migrationBuilder(): MigrationBuilder
    {
        return $this->migrationBuilder;
    }

    public function alterTableBuilder(): AlterTableBuilder
    {
        return $this->alterTableBuilder;
    }

    public function createTableBuilder(): CreateTableBuilder
    {
        return $this->createTableBuilder;
    }

    public function fieldHandler(): FieldHandler
    {
        return $this->fieldHandler;
    }

    public function selectQueryBuilder(): SelectQueryBuilder
    {
        return $this->selectQueryBuilder;
    }

    public function typeHandler(): TypeHandler
    {
        return $this->typeHandler;
    }
}
