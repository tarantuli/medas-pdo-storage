<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\StorageManager\Structure\Blueprint\Field;

interface TypeHandler
{
    public function getBaseDefinition(Field $field): string|null;
}
