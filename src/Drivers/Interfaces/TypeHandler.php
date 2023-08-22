<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\Database;
use Medas\StorageManager\Structure\Blueprint\Field;

interface TypeHandler
{
    public function getBaseDefinition(Database $database, Field $field): string|null;
}
