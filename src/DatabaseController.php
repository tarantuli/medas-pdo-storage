<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\PdoStorage\Drivers\DriverHandler;

class DatabaseController
{
    public function __construct(
        public readonly \PDO          $pdo,
        public readonly Transaction   $transaction,
        public readonly DriverHandler $driverHandler,
    )
    {
    }
}
