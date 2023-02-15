<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases\TableStructureFinder;

use Medas\Core\Str;
use Medas\EntityManager\Types\Integer;
use Medas\StorageManager\Structure\Blueprint\{Field, Type};

class DefinitionHandler
{
    const CREATION_TIMESTAMP_DEFINITION = ' default current_timestamp()';
    const MODIFICATION_TIMESTAMP_DEFINITION = ' default current_timestamp() on update current_timestamp()';

    public function convertToField(string $name, string $definition): Field
    {
        $isNullable = true;
        $isGenerated = false;

        $isCreationTimestamp = false;
        $isModificationTimestamp = false;

        // Strip collation
        $definition = preg_replace('/ collate \w+/i', '', $definition);

        if (Str::endsWithCi($definition, ' auto_increment')) {
            $isNullable = false;
            $isGenerated = true;
            $definition = substr($definition, 0, -strlen(' auto_increment'));
        }

        if (Str::endsWithCi($definition, self::CREATION_TIMESTAMP_DEFINITION)) {
            $isCreationTimestamp = true;
            $definition = substr($definition, 0, -strlen(self::CREATION_TIMESTAMP_DEFINITION));
        }

        if (Str::endsWithCi($definition, self::MODIFICATION_TIMESTAMP_DEFINITION)) {
            $isModificationTimestamp = true;
            $definition = substr($definition, 0, -strlen(self::MODIFICATION_TIMESTAMP_DEFINITION));
        }

        if (preg_match('/^(.+) default (.+)$/i', $definition, $defaultMatch)) {
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

        if (Str::endsWithCi($definition, ' not null')) {
            $isNullable = false;
            $definition = substr($definition, 0, -strlen(' not null'));
        }

        $isInt = preg_match('/((?:tiny|small|medium|big)?int)(?:\(\d+\))?( unsigned)?/i', $definition, $intMatch);

        $type = match (true) {
            (bool) $isInt => Type::Integer,
            Str::startsWithCi($definition, 'varchar(') || Str::startsWithCi($definition, 'char(') => Type::Text,
            Str::startsWithCi($definition, 'varbinary(') || Str::startsWithCi($definition, 'binary(') => Type::Binary,
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
                    'smallint' => Integer::UNSIGNED_2_BYTE_MAX,
                    'mediumint' => Integer::UNSIGNED_3_BYTE_MAX,
                    'int' => Integer::UNSIGNED_4_BYTE_MAX,
                    default => Integer::UNSIGNED_8_BYTE_MAX,
                };
            }
            else {
                $minValue = match ($intMatch[1]) {
                    'tinyint' => Integer::SIGNED_1_BYTE_MIN,
                    'smallint' => Integer::SIGNED_2_BYTE_MIN,
                    'mediumint' => Integer::SIGNED_3_BYTE_MIN,
                    'int' => Integer::SIGNED_4_BYTE_MIN,
                    default => Integer::SIGNED_8_BYTE_MIN,
                };
                $maxValue = match ($intMatch[1]) {
                    'tinyint' => Integer::SIGNED_1_BYTE_MAX,
                    'smallint' => Integer::SIGNED_2_BYTE_MAX,
                    'mediumint' => Integer::SIGNED_3_BYTE_MAX,
                    'int' => Integer::SIGNED_4_BYTE_MAX,
                    default => Integer::SIGNED_8_BYTE_MAX,
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
        if (strcasecmp($string, 'null') === 0) {
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
