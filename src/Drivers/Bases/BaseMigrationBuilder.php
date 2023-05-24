<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\FileBuilder\PhpClass\MethodDefinition;
use Medas\PdoStorage\Database;
use Medas\PdoStorage\Exceptions\StorageIsNotDatabase;
use Medas\PdoStorage\Queries\{Query, QueryCollection};
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Migrations\MigrationBuilder;
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\Structure\Changes\ChangeFinder;
use Medas\StorageManager\Structure\EntityStructureFinder;
use Medas\StorageManager\UnitOfWork\Priority;

abstract class BaseMigrationBuilder implements MigrationBuilder
{
    private Database $database;

    public function __construct(
        private readonly ChangeFinder          $changeFinder,
        private readonly EntityStructureFinder $entityStructureFinder,
        private readonly StorageManager        $storageManager,
    )
    {
    }

    public function build(
        Storage          $storage,
        string           $className,
        MethodDefinition $migrateMethod,
        MethodDefinition $undoMethod,
    ): bool
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if (!$storage instanceof Database) {
            throw new StorageIsNotDatabase($this->storageManager->getName($storage));
        }

        $this->database = $storage;

        $queries = $this->buildQueries($className);

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

    private function buildQueries(string $className): QueryCollection|null
    {
        $driver = $this->database->controller()->driver();

        $expectedStructure = $this->entityStructureFinder->find($className);
        $existingStructure = $driver->tableStructureFinder()->find($this->database->store($expectedStructure->name()));

        if ($existingStructure === null) {
            return $this->database->controller()->actionBuilder()->createStore($expectedStructure);
        }
        else {
            $changes = $this->changeFinder->find($expectedStructure, $existingStructure);
            return $changes ? $driver->alterTableBuilder()->create($expectedStructure, $changes) : null;
        }
    }
}
