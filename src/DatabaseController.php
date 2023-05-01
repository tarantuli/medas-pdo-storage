<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Interfaces\Serializer;
use Medas\PdoStorage\Drivers\DriverHandler;
use Medas\PdoStorage\Queries\Query;
use Medas\StorageManager\Interfaces\{ActionBuilder, StorageController};
use Medas\StorageManager\Migrations\MigrationBuilder;

class DatabaseController implements StorageController
{
    private \PDO $pdo;
    private Executor $executor;
    private Transaction $transaction;
    private DriverHandler $driver;

    public function __construct(
        private readonly Database $database,
        private readonly string   $dns,
        private readonly string   $username,
        private readonly string   $password,
    )
    {
        $this->initializePdo();
        $this->initializeDriverHandler();
        $this->executor = new Executor();
    }

    private function initializePdo(): void
    {
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_PERSISTENT => true,
        ];

        $this->pdo = new \PDO($this->dns, $this->username, $this->password, $options);

        $this->transaction = new Transaction($this->pdo);
    }

    private function initializeDriverHandler(): void
    {
        $driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $this->driver = service(DriverHandlerManager::class)->find($driver);
    }

    public function transaction(): Transaction
    {
        return $this->transaction;
    }

    public function deleteStore(string $name): void
    {
        $this->execute($this->actionBuilder()->dropTable($name));
    }

    public function execute(Query $query): void
    {
        $this->executor->execute($this->pdo, $query);
    }

    public function actionBuilder(): ActionBuilder
    {
        return $this->driver->queryBuilder();
    }

    public function serializer(): Serializer
    {
        return $this->driver->serializer();
    }

    public function migrationBuilder(): MigrationBuilder
    {
        return $this->driver->migrationBuilder();
    }

    public function driver(): DriverHandler
    {
        return $this->driver;
    }

    public function lastGeneratedValue(): int|null
    {
        $id = $this->pdo->lastInsertId();

        return $id === false ? null : (int) $id;
    }

    public function escapeValue(mixed $value): string
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

    public function database(): Database
    {
        return $this->database;
    }
}
