<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure;

use Medas\FileBuilder\PhpClass\MethodDefinition;
use Medas\PdoStorage\Database;
use Medas\PdoStorage\Exceptions\StorageIsNotDatabaseException;
use Medas\PdoStorage\Queries\{AlterTableBuilder, Query};
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Migrations\MigrationBuilder;
use Medas\StorageManager\StorageManager;

#[Service]
class TableMigrationBuilder implements MigrationBuilder
{
    private Database $database;

    public function __construct(
        private readonly ChangeFinder          $changeFinder,
        private readonly EntityStructureFinder $entityStructureFinder,
        private readonly StorageManager        $storageManager,
        private readonly TableStructureFinder  $tableStructureFinder,
    )
    {
    }

    public function build(Storage $storage, string $className, MethodDefinition $migrateMethod, MethodDefinition $undoMethod): void
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if (!$storage instanceof Database) {
            throw new StorageIsNotDatabaseException($this->storageManager->getName($storage));
        }

        $this->database = $storage;

        if (null === $query = $this->buildQuery($className)) {
            return;
        }

        $queryClass = Query::class;
        $query = addslashes(trim($query->query));
        $databaseName = $this->storageManager->getName($this->database);

        $argumentsAndDatabase = $databaseName === 'default'
            ? ''
            : sprintf(', [], storage("%s")', addslashes($databaseName));

        $migrateMethod->body .= <<<PHP
            \$unitOfWork->addAction(new \\$queryClass("$query"$argumentsAndDatabase));
        PHP;

    }

    private function buildQuery(string $className): Query|null
    {
        $expectedStructure = $this->entityStructureFinder->find($className);
        $existingStructure = $this->tableStructureFinder->find($this->database->store($expectedStructure->name));

        if ($existingStructure === null) {
            return $this->database->queryBuilder()->createTable($expectedStructure);
        }
        else {
            $changes = $this->changeFinder->find($expectedStructure, $existingStructure);
            return $changes ? (new AlterTableBuilder($changes))->create($this->database) : null;
        }
    }
}
