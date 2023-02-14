<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\EntityManager\Types\Integer;
use Medas\PdoStorage\Drivers\Interfaces\TypeHandler;
use Medas\StorageManager\Structure\Blueprint\{Field, Type};

abstract class BaseTypeHandler implements TypeHandler
{
    public function getBaseDefinition(Field $field): string
    {
        return match ($field->type) {
            Type::Text => $this->handleText($field),
            Type::Binary => $this->handleBinary($field),
            Type::DateTime => $this->handleDateTime(),
            Type::Integer => $this->handleInteger($field),
            Type::Boolean => $this->handleBoolean(),
            Type::Float => $this->handleFloat(),
        };
    }

    private function handleText(Field $field): string
    {
        /** @noinspection PhpDuplicateMatchArmBodyInspection */
        return match (true) {
            $field->maxLength <= Integer::UNSIGNED_1_BYTE_MAX => $field->minLength === $field->maxLength
                ? sprintf('CHAR(%u)', $field->maxLength)
                : sprintf('VARCHAR(%u)', $field->maxLength),
            $field->maxLength <= Integer::UNSIGNED_2_BYTE_MAX => 'TEXT',
            $field->maxLength <= Integer::UNSIGNED_3_BYTE_MAX => 'MEDIUMTEXT',
            $field->maxLength <= Integer::UNSIGNED_4_BYTE_MAX => 'LONGTEXT',
            default => 'TEXT'
        };
    }

    private function handleBinary(Field $field): string
    {
        /** @noinspection PhpDuplicateMatchArmBodyInspection */
        return match (true) {
            $field->maxLength <= Integer::UNSIGNED_1_BYTE_MAX => $field->minLength === $field->maxLength
                ? sprintf('BINARY(%u)', $field->maxLength)
                : sprintf('VARBINARY(%u)', $field->maxLength),
            $field->maxLength <= Integer::UNSIGNED_2_BYTE_MAX => 'BLOB',
            $field->maxLength <= Integer::UNSIGNED_3_BYTE_MAX => 'MEDIUMBLOB',
            $field->maxLength <= Integer::UNSIGNED_4_BYTE_MAX => 'LONGBLOB',
            default => 'BLOB'
        };
    }

    private function handleDateTime(): string
    {
        return 'DATETIME';
    }

    private function handleFloat(): string
    {
        return 'FLOAT';
    }

    private function handleInteger(Field $field): string
    {
        /** @noinspection PhpDuplicateMatchArmBodyInspection */
        return match (true) {
            $field->minValue >= 0 && $field->maxValue <= Integer::UNSIGNED_1_BYTE_MAX => 'TINYINT UNSIGNED',
            $field->minValue >= 0 && $field->maxValue <= Integer::UNSIGNED_2_BYTE_MAX => 'MEDIUMINT UNSIGNED',
            $field->minValue >= 0 && $field->maxValue <= Integer::UNSIGNED_3_BYTE_MAX => 'INT UNSIGNED',
            $field->minValue >= 0 && $field->maxValue <= Integer::UNSIGNED_4_BYTE_MAX => 'BIGINT UNSIGNED',

            $field->minValue >= Integer::SIGNED_1_BYTE_MAX && $field->maxValue <= Integer::SIGNED_1_BYTE_MAX => 'TINYINT',
            $field->minValue >= Integer::SIGNED_2_BYTE_MAX && $field->maxValue <= Integer::SIGNED_2_BYTE_MAX => 'MEDIUMINT',
            $field->minValue >= Integer::SIGNED_3_BYTE_MAX && $field->maxValue <= Integer::SIGNED_3_BYTE_MAX => 'INT',
            $field->minValue >= Integer::SIGNED_4_BYTE_MAX && $field->maxValue <= Integer::SIGNED_4_BYTE_MAX => 'BIGINT',

            default => 'INT UNSIGNED'
        };
    }

    private function handleBoolean(): string
    {
        return 'TINYINT UNSIGNED';
    }
}
