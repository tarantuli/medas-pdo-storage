<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\Drivers\DriverHandlerFinder;

#[Service]
class DatabaseControllerInitializer
{
    public function __construct(
        private readonly DriverHandlerFinder $driverHandlerFinder,
    )
    {
    }

    public function initialize(Database $database): DatabaseController
    {
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_PERSISTENT => true,
        ];

        $pdo = new \PDO($database->dns, $database->username, $database->password, $options);
        $transaction = new Transaction($pdo);
        $driverHandler = $this->driverHandlerFinder->find($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));

        return new DatabaseController($pdo, $transaction, $driverHandler);
    }
}
