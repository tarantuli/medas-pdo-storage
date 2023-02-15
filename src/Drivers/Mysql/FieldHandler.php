<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Mysql;

use Medas\EntityManager\Types\Binary;
use Medas\EntityManager\Types\Boolean;
use Medas\EntityManager\Types\DateTime;
use Medas\EntityManager\Types\FloatingPoint;
use Medas\EntityManager\Types\Integer;
use Medas\EntityManager\Types\Text;
use Medas\EntityManager\Types\Type as EntityType;
use Medas\PdoStorage\Drivers\Bases\BaseFieldHandler;
use Medas\StorageManager\Structure\Blueprint\Field;
use Medas\StorageManager\Structure\Blueprint\Type as BlueprintType;

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

    private function blueprintToEntityType(BlueprintType $type): EntityType
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
