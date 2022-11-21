<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\StorageManager\Interfaces\Transaction as TransactionInterface;

class Transaction implements TransactionInterface
{
    public function __construct(
        private readonly \PDO $pdo,
    )
    {
    }

    public function begin(): void
    {
        $this->pdo->beginTransaction();
    }

    public function rollback(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function commit(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }
}
