<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\FileBuilder\PhpClass\MethodDefinition;
use Medas\PdoStorage\Database;
use Medas\PdoStorage\Queries\{Query, QueryCollection};
use Medas\StorageManager\Migrations\MigrationBuilder;
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\Structure\Changes\ChangeFinder;
use Medas\StorageManager\Structure\EntityStructureFinder;
use Medas\StorageManager\UnitOfWork\Priority;

abstract class BaseMigrationBuilder implements MigrationBuilder
{
    public function __construct(
        private readonly ChangeFinder          $changeFinder,
        private readonly EntityStructureFinder $entityStructureFinder,
        private readonly Database              $database,
    )
    {
    }

    public function build(
        string           $className,
        MethodDefinition $migrateMethod,
        MethodDefinition $undoMethod,
    ): bool
    {
        $expectedStructure = $this->entityStructureFinder->find($className);
        $queries = $this->buildQueries($expectedStructure);

        if (count($queries) === 0) {
            return false;
        }

        $queryClass = Query::class;
        $priorityClass = Priority::class;

        foreach ($queries as $query) {
            $queryString = addcslashes(trim($query->query), '"');

            $migrateMethod->body .= <<<PHP
\$unitOfWork->addAction(new \\$queryClass(
    query: <<<SQL
$queryString
SQL,
    priority: \\$priorityClass::{$query->priority()->name}
));
PHP;
        }

        return true;
    }

    public function buildQueries(Blueprint $expectedStructure): QueryCollection|null
    {
        $driver = $this->database->controller()->driver();

        $existingStructure = $driver->tableStructureFinder()->find($this->database->store($expectedStructure->name()));

        if ($existingStructure === null) {
            return $driver->createTableBuilder()->create($expectedStructure);
        }
        else {
            $changes = $this->changeFinder->find($expectedStructure, $existingStructure);
            return $changes ? $driver->alterTableBuilder()->create($expectedStructure, $changes) : null;
        }
    }
}
