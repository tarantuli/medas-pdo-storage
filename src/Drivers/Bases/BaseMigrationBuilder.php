<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\FileBuilder\PhpClass\MethodDefinition;
use Medas\PdoStorage\Database;
use Medas\PdoStorage\Exceptions\StorageIsNotDatabaseException;
use Medas\PdoStorage\Queries\Query;
use Medas\PdoStorage\Structure\ChangeFinder;
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Migrations\MigrationBuilder;
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\Structure\EntityStructureFinder;

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
            throw new StorageIsNotDatabaseException($this->storageManager->getName($storage));
        }

        $this->database = $storage;

        if (null === $query = $this->buildQuery($className)) {
            return false;
        }

        $queryClass = Query::class;
        $query = addcslashes(trim($query->query), '"');
        $databaseName = $this->storageManager->getName($this->database);

        $argumentsAndDatabase = $databaseName === 'default'
            ? ''
            : sprintf(', [], storage("%s")', addslashes($databaseName));

        $migrateMethod->body .= <<<PHP
            \$unitOfWork->addAction(new \\$queryClass("$query"$argumentsAndDatabase));
        PHP;

        return true;
    }

    private function buildQuery(string $className): Query|null
    {
        $expectedStructure = $this->entityStructureFinder->find($className);
        $existingStructure = $this->database->controller()->driver()->tableStructureFinder()->find(
            $this->database->store($expectedStructure->name())
        );

        if ($existingStructure === null) {
            return $this->database->controller()->actionBuilder()->createStore($expectedStructure);
        }
        else {
            $changes = $this->changeFinder->find($expectedStructure, $existingStructure);
            return $changes ? $this->database->controller()->driver()->alterTableBuilder()->create($changes) : null;
        }
    }
}
