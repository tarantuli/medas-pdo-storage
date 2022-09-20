<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure\Blueprint;

class ForeignKey
{
    public function __construct(
        public string $field,
        public string $foreignEntity,
        public string $foreignField,
    )
    {
    }
}
