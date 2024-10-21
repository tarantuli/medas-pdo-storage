<?php

declare(strict_types=1);

namespace Medas\PdoStorage\MetaData;

use Medas\StorageManager\Interfaces\RecordSetMetaData;

class MetaData implements RecordSetMetaData
{
    /** @var ColumnData[] */
    public array $fields = [];

    /** @var string[] */
    public array $fieldNames = [];

    /** @var string[] */
    public array $primaryKeyFieldNames = [];

    public int $rowCount;

    public function fields(): array
    {
        return $this->fields;
    }

    public function fieldNames(): array
    {
        return $this->fieldNames;
    }

    public function primaryKeyFieldNames(): array
    {
        return $this->primaryKeyFieldNames;
    }

    public function rowCount(): int
    {
        return $this->rowCount;
    }
}
