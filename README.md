# medas-pdo-storage

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

## Description

The PDO-based storage engine shared by `medas-pdo-mysql` and `medas-pdo-sqlite`. It implements `StorageController` for `medas-storage-manager` and provides the connection management, query execution, transaction handling, and schema management infrastructure that driver-specific packages build on.

**Architecture:**

`PdoStorageController` is registered with `StorageManager` on package initialization. It maintains one `DatabaseController` per named `Database` connection, each holding a `\PDO` instance, a `Transaction` wrapper, and the `DriverHandler` resolved for the connection's DSN prefix.

`DriverHandlerManager` holds a prioritised registry of `DriverHandler` implementations. When a new `Database` is initialized, `DatabaseControllerInitializer` extracts the driver name from the DSN (e.g. `mysql`, `sqlite`) and asks `DriverHandlerManager` for the matching handler. Driver packages (`medas-pdo-mysql`, `medas-pdo-sqlite`) register themselves here during their own `ready()` / `initialize()` calls.

`QueryExecutor` implements `ActionExecutor`: it prepares and executes parameterized SQL queries, serializes bound arguments via the driver's `Serializer`, propagates `lastInsertId`, and wraps PDO exceptions in typed `StorageException` instances using the driver's `ExceptionTypeFinder`.

On shutdown, `PdoStoragePackage` registers a shutdown function that rolls back any open transaction to prevent lock leaks on persistent connections.

**Key classes for driver implementors:**

| Class / Interface      | Purpose                                                                          |
|------------------------|----------------------------------------------------------------------------------|
| `Database`             | `Storage` implementation — DSN, credentials, name, persistent flag               |
| `DatabaseController`   | Per-connection holder of `PDO`, `Transaction`, and `DriverHandler`               |
| `DriverHandler`        | Interface driver packages implement                                              |
| `DriverHandlerManager` | Registry of `DriverHandler` implementations; resolves by DSN prefix              |
| `Transaction`          | `begin()`, `commit()`, `rollback()` over a `PDO` instance                        |
| `QueryExecutor`        | Executes `Query` actions; wraps PDO errors as typed `StorageException`           |
| `SelectorQueryBuilder` | Translates `Selector` definitions to SQL WHERE/ORDER/LIMIT clauses               |
| `StoreQueryBuilder`    | Builds complex SELECT queries with relations, grouping, pagination, calculations |

## Configuration options

| Option                              | Default                         | Description                                                                  |
|-------------------------------------|---------------------------------|------------------------------------------------------------------------------|
| `pdo.dsn`                           | none (required)                 | PDO DSN string, e.g. `mysql:host=...;dbname=...`                             |
| `pdo.username`                      | none                            | Database username                                                            |
| `pdo.password`                      | none                            | Database password                                                            |
| `pdo.name`                          | none                            | Logical name for this connection (referenced by `#[Entity(storage: '...')]`) |
| `pdo.persistent-connection`         | `false`                         | Whether to use persistent PDO connections                                    |
| `join-tables.table-naming-strategy` | `DoubleUnderscoreConcatenation` | Strategy for naming join tables for collection properties                    |

## Usage

### Package developer context

`pdo-storage` is not used directly — it is a shared dependency of `medas-pdo-mysql` and `medas-pdo-sqlite`. Register one of those driver packages instead:

```php
// For MySQL
use Medas\PdoMysql\PdoMysqlPackage;
PdoMysqlPackage::instance();

// For SQLite
use Medas\PdoSqlite\PdoSqlitePackage;
PdoSqlitePackage::instance();
```

**Working with transactions:**

```php
use Medas\PdoStorage\PdoStorageController;
use Medas\Core\Attributes\Service;

#[Service]
readonly class PaymentService
{
    public function __construct(
        private PdoStorageController $pdoStorageController,
    ) {}

    public function processPayment(Order $order, Payment $payment): void
    {
        $transaction = $this->pdoStorageController->transaction();

        $transaction->begin();

        try {
            $this->entityManager->persist($order);
            $this->entityManager->persist($payment);
            $this->entityManager->flush();

            $transaction->commit();
        }
        catch (\Throwable $e) {
            $transaction->rollback();
            throw $e;
        }
    }
}
```

**Listing all tables in a database:**

```php
$tables = $this->pdoStorageController->getStores();

foreach ($tables as $table) {
    echo $table->name() . "\n";
}

// Filter by prefix
$invoiceTables = $this->pdoStorageController->getStores(nameFilter: 'invoice');
```

**Executing a raw query** (for cases not covered by the entity manager):

```php
use Medas\PdoStorage\Queries\{Query, QueryExecutor};
use Medas\StorageManager\UnitOfWork\{ActionSet, Priority};

$query = new Query(
    query: 'UPDATE invoices SET status = ? WHERE created_at < ?',
    arguments: ['archived', '2025-01-01'],
    storage: $database,
    priority: Priority::Normal,
);

$actionSet = new ActionSet();
$actionSet->addAction($query);

$this->queryExecutor->executeSet($actionSet);
```

**Implementing a custom `DriverHandler`** — extend this package to support a new PDO database dialect:

```php
use Medas\PdoStorage\Drivers\DriverHandler;
use Medas\PdoStorage\Drivers\DriverHandlerManager;
use Medas\Core\Attributes\Service;

#[Service]
readonly class PostgresHandler implements DriverHandler
{
    public function canHandle(string $driverName): bool
    {
        return $driverName === 'pgsql';
    }

    public function priority(): int
    {
        return -300;
    }

    public function quote(Database $database, string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    // ... implement the rest of the interface
}

// Register in your package's initialize() or ready() method:
service(DriverHandlerManager::class)->addHandler(service(PostgresHandler::class));
```

**Enabling query debug logging:**

```yaml
core:
  dispatch-debug-information: true
```

With this option enabled, `QueryExecutor` dispatches `DebugInformation` events for every executed query, including the SQL string and bound arguments. Listen to them with `medas-logging` or your own `#[EventListener]`.

### Backend user context

This package is infrastructure — backend developers interact with it indirectly through the entity manager and `medas-pdo-mysql` or `medas-pdo-sqlite`. The main configuration points are covered in those packages' READMEs.

**Transaction safety** — a PHP shutdown function is registered that rolls back any open transaction. This prevents lock leaks when a script ends unexpectedly (uncaught exception, `exit()`, fatal error) while a transaction is still open. For normal request flow the application code is responsible for calling `commit()` or `rollback()`.

**Join table naming** — collection properties (one-to-many or many-to-many relations) are stored in join tables. The default naming strategy concatenates the two table names with `__` (e.g. `orders__tags`). Override via `join-tables.table-naming-strategy` if your schema uses a different convention.

**`DriverNotImplemented`** — thrown when a DSN uses a driver prefix for which no `DriverHandler` is registered (e.g. `pgsql:` without a PostgreSQL driver package). Install the matching driver package or implement your own `DriverHandler`.
