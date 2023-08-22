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

    private function getDatabaseController(Database $database): DatabaseController
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
        return $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler->table($name);
    }

    public function actionBuilders(Storage $storage = null): ActionBuilders
    {
        return $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler->queryBuilders();
    }

    public function serializer(Storage $storage = null): Serializer
    {
        return $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler->serializer();
    }

    public function migrationBuilder(Storage $storage = null): MigrationBuilder
    {
        return $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler->migrationBuilder();
    }

    public function lastGeneratedValue(Storage $storage = null): int|null
    {
        $id = $this->getDatabaseController($storage ?? $this->defaultDatabase)->pdo->lastInsertId();

        return $id === false ? null : (int) $id;
    }

    public function hasStore(Store $store, Storage $storage = null): bool
    {
        $query = $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler->queryBuilders()->showTables()->build($storage->name());
        $query->execute();

        return $query->recordSet()->hasRecords();
    }

    public function recordFetchers(Storage $storage = null): RecordFetchers
    {
        return $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler->recordFetchers();
    }

    /*        private readonly QueryExecutor                 $executor,
            private readonly JoinTableManager              $joinTableManager,*/
    //    public function execute(Query $query): void
    //    {
    //        $this->executor->execute($this->getDatabaseController($query->storage())->pdo, $query);
    //    }
    //
    /*    public function escapeValue(mixed $value): string
        {
            if (null === $value) {
                return 'null';
            }

            if (is_object($value) && enum_exists($value::class)) {
                $value = $value->value;
            }

            if (is_bool($value)) {
                $value = (int) $value;
            }

            return $this->pdo->quote((string) $value);
        }
    */
    /*    public function fetchAll(Store $store, array $filters, Storage $storage = null): array|null
        {
            $storage ??= $this->defaultDatabase;
    
            $query = $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler->queryBuilders()->select([$store], $filters);
            $query->execute();
    
            return $query->recordSet()->fetchRecords();
        }
    
        public function fetchCollectionRecord(Store $store, object $entity, Property $property, Storage $storage = null): iterable
        {
            $storage ??= $this->defaultDatabase;
    
            $joinTable = $this->joinTableManager->determineName($storage->name(), $property->name);
    
            return $this->fetchAll($this->store($joinTable, $storage ?? $this->defaultDatabase), ['id' => $entity]);
        }
    
        public function fetchRecord(Store $store, array $filters, Storage $storage = null): Record|null
        {
            $storage ??= $this->defaultDatabase;
    
            $query = $this->getDatabaseController($storage ?? $this->defaultDatabase)->driverHandler->queryBuilders()->select([$store], $filters);
            $query->execute();
    
            return $query->recordSet()->fetchRecord();
        }*/
}
