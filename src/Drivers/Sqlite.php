<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

use Medas\Core\GlobalRepository;

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

        $oi = GlobalRepository::objectInstantiator();

        $this->tableStructureFinder = $oi->instantiate(Sqlite\TableStructureFinder::class, $givenArguments);
        $this->alterTableBuilder = $oi->instantiate(Sqlite\AlterTableBuilder::class, $givenArguments);
        $this->createTableBuilder = $oi->instantiate(Sqlite\CreateTableBuilder::class, $givenArguments);
        $this->fieldHandler = $oi->instantiate(Sqlite\FieldHandler::class, $givenArguments);
        $this->migrationBuilder = $oi->instantiate(Sqlite\MigrationBuilder::class, $givenArguments);
        $this->queryBuilder = $oi->instantiate(Sqlite\QueryBuilder::class, $givenArguments);
        $this->selectQueryBuilder = $oi->instantiate(Sqlite\SelectQueryBuilder::class, $givenArguments);
        $this->serializer = $oi->instantiate(Sqlite\Serializer::class, $givenArguments);
        $this->typeHandler = $oi->instantiate(Sqlite\TypeHandler::class, $givenArguments);
    }
}
