<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Mysql;

use Medas\PdoStorage\Drivers\Bases\BaseFieldHandler;
use Medas\StorageManager\Structure\Blueprint\Field;

class FieldHandler extends BaseFieldHandler
{
    public function buildDefinition(Field $field): string
    {
        return $this->driver->typeHandler()->getBaseDefinition($field)
            . ($field->isNullable ? '' : ' NOT NULL')
            . ($field->isGenerated ? ' AUTO_INCREMENT' : '')
            . ($field->hasDefault ? ' DEFAULT ' . $this->driver->escape($field->default) : '');
    }
}
