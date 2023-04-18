<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

use Medas\Core\GlobalRepository;

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

        $oi = GlobalRepository::objectInstantiator();

        $this->tableStructureFinder = $oi->instantiate(Mysql\TableStructureFinder::class, $givenArguments);
        $this->alterTableBuilder = $oi->instantiate(Mysql\AlterTableBuilder::class, $givenArguments);
        $this->createTableBuilder = $oi->instantiate(Mysql\CreateTableBuilder::class, $givenArguments);
        $this->fieldHandler = $oi->instantiate(Mysql\FieldHandler::class, $givenArguments);
        $this->migrationBuilder = $oi->instantiate(Mysql\MigrationBuilder::class, $givenArguments);
        $this->queryBuilder = $oi->instantiate(Mysql\QueryBuilder::class, $givenArguments);
        $this->selectQueryBuilder = $oi->instantiate(Mysql\SelectQueryBuilder::class, $givenArguments);
        $this->serializer = $oi->instantiate(Mysql\Serializer::class, $givenArguments);
        $this->typeHandler = $oi->instantiate(Mysql\TypeHandler::class, $givenArguments);
    }
}
