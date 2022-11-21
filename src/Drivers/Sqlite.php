<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

class Sqlite extends BaseDriver
{
    public function quote(string $identifier): string
    {
        return '"' . $identifier . '"';
    }

    public function escape(mixed $value): string
    {
        return $this->controller->escapeValue($value);
    }

    protected function initialize(): void
    {
        $givenArguments = [
            'driver' => $this,
            'controller' => $this->controller,
            'database' => $this->controller->database(),
        ];

        $this->tableStructureFinder = sm()->instantiate(Sqlite\TableStructureFinder::class, $givenArguments);
        $this->alterTableBuilder = sm()->instantiate(Sqlite\AlterTableBuilder::class, $givenArguments);
        $this->createTableBuilder = sm()->instantiate(Sqlite\CreateTableBuilder::class, $givenArguments);
        $this->fieldHandler = sm()->instantiate(Sqlite\FieldHandler::class, $givenArguments);
        $this->migrationBuilder = sm()->instantiate(Sqlite\MigrationBuilder::class, $givenArguments);
        $this->queryBuilder = sm()->instantiate(Sqlite\QueryBuilder::class, $givenArguments);
        $this->selectQueryBuilder = sm()->instantiate(Sqlite\SelectQueryBuilder::class, $givenArguments);
        $this->serializer = sm()->instantiate(Sqlite\Serializer::class, $givenArguments);
        $this->typeHandler = sm()->instantiate(Sqlite\TypeHandler::class, $givenArguments);
    }
}
