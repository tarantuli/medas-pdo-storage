<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Mysql;

use Medas\EntityManager\Types\{Binary, Boolean, DateTime, FloatingPoint, Integer, Text};
use Medas\PdoStorage\Drivers\Bases\BaseFieldHandler;
use Medas\ServiceManager\Interfaces\Type;
use Medas\StorageManager\Structure\Blueprint\{Field, Type as BlueprintType};

class FieldHandler extends BaseFieldHandler
{
    public function buildDefinition(Field $field): string
    {
        if ($field->isCreationTimestamp || $field->isModificationTimestamp) {
            $default = ' default current_timestamp()';

            if ($field->isModificationTimestamp) {
                $default .= ' on update current_timestamp()';
            }
        }
        elseif ($field->hasDefault) {
            $serializedDefault = $this->driver->serializer()->serialize($this->blueprintToEntityType($field->type), $field->default);
            $default = ' default ' . $this->driver->escape($serializedDefault);
        }
        else {
            $default = '';
        }

        return $this->driver->typeHandler()->getBaseDefinition($field)
            . ($field->isNullable ? '' : ' not null')
            . ($field->isGenerated ? ' auto_increment' : '')
            . $default;
    }

    private function blueprintToEntityType(BlueprintType $type): Type
    {
        return match ($type) {
            BlueprintType::Boolean => new Boolean(),
            BlueprintType::Binary => new Binary(),
            BlueprintType::DateTime => new DateTime(),
            BlueprintType::Integer => new Integer(),
            BlueprintType::Text => new Text(),
            BlueprintType::Float => new FloatingPoint(),
        };
    }
}
