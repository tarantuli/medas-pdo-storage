<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\EntityManager\Types\{Binary, Boolean, DateTime, Integer, Text};
use Medas\StorageManager\{Entities\Record, Interfaces\RecordMetaData, Interfaces\RecordSet};

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

    public function fetchMetaData(): RecordMetaData
    {
        $metaData = new MetaData\MetaData();

        for ($column = 0; $column < $this->pdoStatement->columnCount(); ++$column) {
            $data = $this->pdoStatement->getColumnMeta($column);
            $type = match ($data['native_type']) {
                'BOOLEAN' => new Boolean(),
                'DOUBLE', 'LONG', 'TINY' => new Integer(),
                'BLOB' => new Binary(),
                'DATE', 'DATETIME' => new DateTime(),
                'VAR_STRING' => new Text(),
            };

            $metaData->fields[] = new MetaData\ColumnData(
                $data['name'],
                $type,
                $data['len'],
                $data['precision'],
            );
        }

        return $metaData;
    }
}
