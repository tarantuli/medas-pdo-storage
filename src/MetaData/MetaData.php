<?php

declare(strict_types=1);

namespace Medas\PdoStorage\MetaData;

use Medas\StorageManager\Interfaces\RecordMetaData;

class MetaData implements RecordMetaData
{
    /** @var ColumnData[] */
    public array $fields = [];

    public int $rowCount;

    public function fields(): array
    {
        return $this->fields;
    }

    public function rowCount(): int
    {
        return $this->rowCount;
    }
}
