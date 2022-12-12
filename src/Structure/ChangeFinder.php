<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure;

use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Structure\Blueprint;

#[Service]
class ChangeFinder
{
    public function find(Blueprint $expected, Blueprint $existing): Changes|null
    {
        $foundChanges = false;
        $changes = new Changes($expected->name());

        foreach ($expected->fields() as $field) {
            if ($current = $existing->fieldByName($field->name)) {
                if ($this->areComparable($field, $current)) {
                    continue;
                }

                $changes->changeFields[] = $field;
            }
            else {
                $changes->addFields[] = $field;
            }

            $foundChanges = true;
        }

        return $foundChanges ? $changes : null;
    }

    private function areComparable(Blueprint\Field $field, Blueprint\Field $current): bool
    {
        $diff = array_udiff_assoc((array) $field, (array) $current, fn($a, $b) => $a <=> $b);

        // If the diff contains length or value parameters, compare ranges
        if (isset($diff['minLength']) and $current->minLength <= $field->minLength) {
            unset($diff['minLength']);
        }

        if (isset($diff['maxLength']) and $current->maxLength >= $field->maxLength) {
            unset($diff['maxLength']);
        }

        if (isset($diff['minValue']) and $current->minValue <= $field->minValue) {
            unset($diff['minValue']);
        }

        if (isset($diff['maxValue']) and $current->maxValue >= $field->maxValue) {
            unset($diff['maxValue']);
        }

        foreach ($diff as $key => $value) {
            $fieldValue = $field->$key;
            $currentValue = $current->$key;

            if ($fieldValue === Blueprint\Type::Boolean && $currentValue === Blueprint\Type::Integer) {
                unset($diff[$key]);
                continue;
            }

            if (is_object($value) && enum_exists($value::class)) {
                // Compare by backed value
                if ($fieldValue->value == $currentValue) {
                    unset($diff[$key]);
                    /** @noinspection PhpUnnecessaryStopStatementInspection */
                    continue;
                }
            }
        }

        return $diff === [];
    }
}
