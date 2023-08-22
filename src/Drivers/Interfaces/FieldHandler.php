<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\Database;
use Medas\StorageManager\Structure\Blueprint\Field;

interface FieldHandler
{
    public function buildDefinition(Database $database, Field $field): string|null;
}
