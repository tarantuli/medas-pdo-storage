<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\StorageManager\{Entities\Record, Interfaces\RecordSet};

readonly class Statement implements RecordSet
{
    public function __construct(
        private \PDOStatement $pdoStatement,
    )
    {
    }

    public function fetchRecord(): Record|null
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
        return $this->pdoStatement->rowCount() && $this->pdoStatement->columnCount();
    }
}
