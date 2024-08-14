<?php

declare(strict_types=1);

namespace Medas\PdoStorage\MetaData;

use Medas\Core\Interfaces\Type;
use Medas\StorageManager\Interfaces\FieldMetaData;

readonly class ColumnData implements FieldMetaData
{
    public function __construct(
        private Type     $type,
        private int|null $length,
        private int|null $precision,
    )
    {
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function length(): int|null
    {
        return $this->length;
    }

    public function precision(): int|null
    {
        return $this->precision;
    }
}
