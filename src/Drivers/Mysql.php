<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

class Mysql extends BaseDriver
{
    public function quote(string $identifier): string
    {
        return '`' . $identifier . '`';
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

        $this->alterTableBuilder = sm()->instantiate(Mysql\AlterTableBuilder::class, $givenArguments);
        $this->createTableBuilder = sm()->instantiate(Mysql\CreateTableBuilder::class, $givenArguments);
        $this->fieldHandler = sm()->instantiate(Mysql\FieldHandler::class, $givenArguments);
        $this->migrationBuilder = sm()->instantiate(Mysql\MigrationBuilder::class, $givenArguments);
        $this->queryBuilder = sm()->instantiate(Mysql\QueryBuilder::class, $givenArguments);
        $this->selectQueryBuilder = sm()->instantiate(Mysql\SelectQueryBuilder::class, $givenArguments);
        $this->serializer = sm()->instantiate(Mysql\Serializer::class, $givenArguments);
        $this->typeHandler = sm()->instantiate(Mysql\TypeHandler::class, $givenArguments);
    }
}
