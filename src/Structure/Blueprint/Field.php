<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure\Blueprint;

class Field
{
    public function __construct(
        public string $name,
        public string $definition,
        public bool   $isNullable = false,
        public bool   $isGenerated = false,
        public bool   $hasDefault = false,
        public mixed  $default = null,
    )
    {
    }
}
