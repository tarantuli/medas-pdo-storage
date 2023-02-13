<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases\TableStructureFinder;

use Medas\EntityManager\Types\Integer;
use Medas\StorageManager\Structure\Blueprint\{Field, Type};

class DefinitionHandler
{
    public function convertToField(string $name, string $definition): Field
    {
        $isNullable = true;
        $isGenerated = false;

        $isCreationTimestamp = false;
        $isModificationTimestamp = false;

        // Strip collation
        $definition = preg_replace('/ COLLATE \w+/', '', $definition);

        if (str_ends_with($definition, ' AUTO_INCREMENT')) {
            $isNullable = false;
            $isGenerated = true;
            $definition = substr($definition, 0, -strlen(' AUTO_INCREMENT'));
        }

        if (str_ends_with($definition, ' DEFAULT current_timestamp()')) {
            $isCreationTimestamp = true;
            $definition = substr($definition, 0, -strlen(' DEFAULT current_timestamp()'));
        }

        if (str_ends_with($definition, ' DEFAULT current_timestamp() ON UPDATE current_timestamp()')) {
            $isModificationTimestamp = true;
            $definition = substr($definition, 0, -strlen(' DEFAULT current_timestamp() ON UPDATE current_timestamp()'));
        }

        if (preg_match('/^(.+) DEFAULT (.+)$/', $definition, $defaultMatch)) {
            $hasDefault = true;
            $default = $this->parseString($defaultMatch[2]);
            $definition = $defaultMatch[1];

            if ($default === null) {
                $hasDefault = false;
            }
        }
        else {
            $hasDefault = false;
            $default = null;
        }

        if (str_ends_with($definition, ' NOT NULL')) {
            $isNullable = false;
            $definition = substr($definition, 0, -strlen(' NOT NULL'));
        }

        $isInt = preg_match('/((?:tiny|medium|big)?int)(?:\(\d+\))?( unsigned)?/', $definition, $intMatch);

        $type = match (true) {
            (bool) $isInt => Type::Integer,
            str_starts_with($definition, 'varchar(') || str_starts_with($definition, 'char(') => Type::Text,
            str_starts_with($definition, 'varbinary(') || str_starts_with($definition, 'binary(') => Type::Binary,
            $definition === 'datetime' => Type::DateTime,
            $definition === 'float' => Type::Float,
            default => throw new \Exception('unhandled definition "' . $definition . '"'),
        };

        $minValue = 0;
        $maxValue = Integer::UNSIGNED_4_BYTE_MAX;

        $minLength = 0;
        $maxLength = Integer::UNSIGNED_1_BYTE_MAX;

        if ($isInt) {
            if (isset($intMatch[2])) {
                $maxValue = match ($intMatch[1]) {
                    'tinyint' => Integer::UNSIGNED_1_BYTE_MAX,
                    'mediumint' => Integer::UNSIGNED_2_BYTE_MAX,
                    'int' => Integer::UNSIGNED_3_BYTE_MAX,
                    default => Integer::UNSIGNED_4_BYTE_MAX,
                };
            }
            else {
                $minValue = match ($intMatch[1]) {
                    'tinyint' => Integer::SIGNED_1_BYTE_MIN,
                    'mediumint' => Integer::SIGNED_2_BYTE_MIN,
                    'int' => Integer::SIGNED_3_BYTE_MIN,
                    default => Integer::SIGNED_4_BYTE_MIN,
                };
                $maxValue = match ($intMatch[1]) {
                    'tinyint' => Integer::SIGNED_1_BYTE_MAX,
                    'mediumint' => Integer::SIGNED_2_BYTE_MAX,
                    'int' => Integer::SIGNED_3_BYTE_MAX,
                    default => Integer::SIGNED_4_BYTE_MAX,
                };
            }
        }

        return new Field(
            name: $name,
            type: $type,
            isNullable: $isNullable,
            isGenerated: $isGenerated,
            isCreationTimestamp: $isCreationTimestamp,
            isModificationTimestamp: $isModificationTimestamp,
            hasDefault: $hasDefault,
            default: $default,
            minValue: $minValue,
            maxValue: $maxValue,
            minLength: $minLength,
            maxLength: $maxLength,
        );
    }

    private function parseString(string $string): string|int|null
    {
        if ($string === 'NULL') {
            return null;
        }

        if (preg_match('/^-?\d+$/', $string)) {
            return (int) $string;
        }

        if (str_starts_with($string, "'") && str_ends_with($string, "'")) {
            return substr($string, 1, -1);
        }

        throw new \Exception('unhandled string structure: ' . $string);
    }
}
