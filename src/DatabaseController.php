<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\PdoStorage\Drivers\DriverHandler;

readonly class DatabaseController
{
    public function __construct(
        public \PDO          $pdo,
        public Transaction   $transaction,
        public DriverHandler $driverHandler,
    )
    {
    }
}
