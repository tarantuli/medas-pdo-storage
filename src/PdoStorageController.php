<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\Serializer;
use Medas\StorageManager\Interfaces\{ActionBuilders, RecordFetchers, Storage, StorageController, Store};
use Medas\StorageManager\Migrations\MigrationBuilder;

#[Service]
class PdoStorageController implements StorageController
{
    private Database $defaultDatabase;

    /** @var DatabaseController[] */
    private array $controllers = [];

    public function __construct(
        private readonly DatabaseControllerInitializer $controllerInitializer,
    )
    {
    }

    public function getDatabaseController(Database $database): DatabaseController
    {
        if (!array_key_exists($database->name, $this->controllers)) {
            $this->initializeDatabaseController($database);
        }

        return $this->controllers[$database->name];
    }

    private function initializeDatabaseController(Database $database): void
    {
        $this->controllers[$database->name] = $this->controllerInitializer->initialize($database);

        if (count($this->controllers) === 1) {
            $this->defaultDatabase = $database;
        }
    }

    public function handles(Storage $storage): bool
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        return $storage instanceof Database;
    }

    public function transaction(Storage $storage = null): Transaction
    {
        return $this->getDatabaseController($storage ?? $this->defaultDatabase)->transaction;
    }

    public function store(string $name, Storage $storage = null): Table
    {
        return $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler
            ->table($storage, $name);
    }

    public function actionBuilders(Storage $storage = null): ActionBuilders
    {
        return $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler
            ->queryBuilders();
    }

    public function serializer(Storage $storage = null): Serializer
    {
        return $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler
            ->serializer();
    }

    public function migrationBuilder(Storage $storage = null): MigrationBuilder
    {
        return $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler
            ->migrationBuilder();
    }

    public function lastGeneratedValue(Storage $storage = null): int|null
    {
        $id = $this->getDatabaseController($storage ?? $this->defaultDatabase)->pdo->lastInsertId();

        return $id === false ? null : (int) $id;
    }

    public function hasStore(Store $store, Storage $storage = null): bool
    {
        $query = $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler
            ->queryBuilders()->showTables()->build($storage, $storage->name());

        $query->execute();

        return $query->recordSet()->hasRecords();
    }

    public function recordFetchers(Storage $storage = null): RecordFetchers
    {
        return $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler
            ->recordFetchers();
    }

    public function quote(Database $database, string $identifier): string
    {
        return $this->getDatabaseController($database)->driverHandler->quote($database, $identifier);
    }

    public function escape(Database $database, mixed $value): string
    {
        return $this->getDatabaseController($database)->driverHandler->escape($database, $value);
    }
}
