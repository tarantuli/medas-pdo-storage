<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\StorageManager\Interfaces\RecordSet;
use Medas\StorageManager\Interfaces\StoreRecord;

readonly class Statement implements RecordSet
{
    public function __construct(
        private \PDOStatement $pdoStatement,
    )
    {
    }

    public function fetchRecord(): StoreRecord|null
    {
        $data = $this->pdoStatement->fetch();
        return is_array($data) ? new Record($data) : null;
    }

    public function fetchRecords(): array
    {
        $data = $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
        $records = [];

        foreach ($data as $set) {
            $records[] = new Record($set);
        }

        return $records;
    }

    public function hasRecords(): bool
    {
        return (bool) $this->pdoStatement->columnCount();
    }
}
