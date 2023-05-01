<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

use Medas\Core\Interfaces\Serializer;
use Medas\PdoStorage\DatabaseController;
use Medas\PdoStorage\Drivers\Interfaces\{AlterTableBuilder,
    CreateTableBuilder,
    FieldHandler,
    QueryBuilder,
    SelectQueryBuilder,
    TableStructureFinder,
    TypeHandler};
use Medas\StorageManager\Migrations\MigrationBuilder;

abstract class BaseHandler implements DriverHandler
{
    protected AlterTableBuilder $alterTableBuilder;
    protected CreateTableBuilder $createTableBuilder;
    protected FieldHandler $fieldHandler;
    protected MigrationBuilder $migrationBuilder;
    protected QueryBuilder $queryBuilder;
    protected SelectQueryBuilder $selectQueryBuilder;
    protected TableStructureFinder $tableStructureFinder;
    protected TypeHandler $typeHandler;
    protected Serializer $serializer;

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

    public function serializer(): Serializer
    {
        return $this->serializer;
    }

    public function migrationBuilder(): MigrationBuilder
    {
        return $this->migrationBuilder;
    }

    public function tableStructureFinder(): TableStructureFinder
    {
        return $this->tableStructureFinder;
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
