<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

readonly class DatabaseController
{
    public function __construct(
        public \PDO                  $pdo,
        public Transaction           $transaction,
        public Drivers\DriverHandler $driverHandler,
    )
    {
    }
}
