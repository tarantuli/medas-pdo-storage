<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\StorageManager\Entities\Record;
use Medas\StorageManager\Interfaces\{RecordSet, RecordSetMetaData};
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
        $data = $this->pdoStatement->fetchAll(\PDO::FETCH_NUM);
        $names = [];

        for ($column = 0; $column < $this->pdoStatement->columnCount(); ++$column) {
            $columnMeta = $this->pdoStatement->getColumnMeta($column);
            $name = $columnMeta['name'];

            if (in_array($name, $names, true)) {
                /** @noinspection PhpArrayKeyDoesNotMatchArrayShapeInspection */
                $name = $columnMeta['table'] . '.' . $name;
            }

            $names[] = $name;
        }

        $records = [];

        foreach ($data as $set) {
            $records[] = new Record(array_combine($names, $set));
        }

        return $records;
    }

    public function hasRecords(): bool
    {
        return $this->pdoStatement->columnCount() > 0;
    }

    public function fetchMetaData(): RecordSetMetaData
    {
        $metaData = new MetaData\MetaData();

        for ($column = 0; $column < $this->pdoStatement->columnCount(); ++$column) {
            $data = $this->pdoStatement->getColumnMeta($column);
            $type = match ($data['native_type']) {
                'BOOLEAN' => Type::Boolean,
                'DOUBLE', 'LONG', 'TINY', 'FLOAT', 'NEWDECIMAL', 'INT24' => Type::Integer,
                'BLOB', 'LONGBLOB' => Type::Binary,
                'DATETIME', 'TIMESTAMP' => Type::DateTime,
                'DATE' => Type::Date,
                default => Type::Text,
            };

            $metaData->fields[] = new MetaData\ColumnData(
                $data['name'],
                $type,
                $data['len'],
                $data['precision'],
                true,
            );

            $metaData->fieldNames[] = $data['name'];
        }

        $metaData->rowCount = $this->pdoStatement->rowCount();

        return $metaData;
    }
}
