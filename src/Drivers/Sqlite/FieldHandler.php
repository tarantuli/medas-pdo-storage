<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Sqlite;

use Medas\PdoStorage\Drivers\Bases\BaseFieldHandler;
use Medas\StorageManager\Structure\Blueprint\Field;

class FieldHandler extends BaseFieldHandler
{
    public function buildDefinition(Field $field): string
    {
        if ($field->isGenerated) {
            return 'integer primary key';
        }

        return $this->driver->typeHandler()->getBaseDefinition($field)
            . ($field->isNullable ? '' : ' not null')
            . ($field->hasDefault ? ' default ' . $this->driver->escape($field->default) : '');
    }
}
