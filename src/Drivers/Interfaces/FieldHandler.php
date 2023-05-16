<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\StorageManager\Structure\Blueprint\Field;

interface FieldHandler
{
    public function buildDefinition(Field $field): string|null;
}
