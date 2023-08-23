<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\Serializer;
use Medas\PdoStorage\Queries\QueryExecutor;
use Medas\StorageManager\Interfaces\{ActionBuilders, ActionExecutor, RecordFetchers, Storage, StorageController, Store};
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
        if ($storage instanceof Database) {
            $this->getDatabaseController($storage);
            return true;
        }

        return false;
    }

    public function transaction(Storage $storage = null): Transaction
    {
        return $this->getDatabaseController($storage ?? $this->defaultDatabase)->transaction;
    }

    public function store(string $name, Storage $storage = null): Table
    {
        $storage ??= $this->defaultDatabase;

        return $this->getDatabaseController($storage)->driverHandler
            ->table($storage, $name);
    }

    public function deleteStore(Store $store): void
    {
        $querySet = $this->getDatabaseController($store->storage())->driverHandler
            ->queryBuilders()->deleteStore()->build($store);

        $this->actionExecutor()->executeSet($querySet);
    }

    public function actionBuilders(): ActionBuilders
    {
        return $this->getDatabaseController($this->defaultDatabase)->driverHandler
            ->queryBuilders();
    }

    public function actionExecutor(): ActionExecutor
    {
        // We can't inject this as a dependency as QueryExecutor depends on this class itself
        return service(QueryExecutor::class);
    }

    public function serializer(Storage $storage = null): Serializer
    {
        return $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler
            ->serializer();
    }

    public function migrationBuilder(): MigrationBuilder
    {
        return $this->getDatabaseController($this->defaultDatabase)->driverHandler
            ->migrationBuilder();
    }

    public function lastGeneratedValue(Storage $storage = null): int|null
    {
        $id = $this->getDatabaseController($storage ?? $this->defaultDatabase)->pdo->lastInsertId();

        return $id === false ? null : (int) $id;
    }

    public function hasStore(Store $store, Storage $storage = null): bool
    {
        $storage ??= $this->defaultDatabase;

        $controller = $this->getDatabaseController($storage);

        $querySet = $controller->driverHandler
            ->queryBuilders()->showTables()->build($storage, $storage->name());

        $this->actionExecutor()->executeSet($querySet);

        return $querySet->recordSet()->hasRecords();
    }

    public function recordFetchers(): RecordFetchers
    {
        return $this->getDatabaseController($this->defaultDatabase)->driverHandler
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
