<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\StorageManager\Entities\Record;
use Medas\StorageManager\Interfaces\{RecordMetaData, RecordSet};
use Medas\StorageManager\Structure\Blueprint\Type;

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
                'BOOLEAN' => Type::Boolean,
                'DOUBLE', 'LONG', 'TINY' => Type::Integer,
                'BLOB' => Type::Binary,
                'DATETIME' => Type::DateTime,
                'DATE' => Type::Date,
                'VAR_STRING', 'STRING' => Type::Text,
            };

            $metaData->fields[] = new MetaData\ColumnData(
                $data['name'],
                $type,
                $data['len'],
                $data['precision'],
                true,
            );
        }

        return $metaData;
    }
}
