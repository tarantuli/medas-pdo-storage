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
                ? sprintf('char(%u)', $field->maxLength)
                : sprintf('varchar(%u)', $field->maxLength),
            $field->maxLength <= Integer::UNSIGNED_2_BYTE_MAX => 'text',
            $field->maxLength <= Integer::UNSIGNED_3_BYTE_MAX => 'mediumtext',
            $field->maxLength <= Integer::UNSIGNED_4_BYTE_MAX => 'longtext',
            default => 'text'
        };
    }

    private function handleBinary(Field $field): string
    {
        /** @noinspection PhpDuplicateMatchArmBodyInspection */
        return match (true) {
            $field->maxLength <= Integer::UNSIGNED_1_BYTE_MAX => $field->minLength === $field->maxLength
                ? sprintf('binary(%u)', $field->maxLength)
                : sprintf('varbinary(%u)', $field->maxLength),
            $field->maxLength <= Integer::UNSIGNED_2_BYTE_MAX => 'blob',
            $field->maxLength <= Integer::UNSIGNED_3_BYTE_MAX => 'mediumblob',
            $field->maxLength <= Integer::UNSIGNED_4_BYTE_MAX => 'longblob',
            default => 'blob'
        };
    }

    private function handleDateTime(): string
    {
        return 'datetime';
    }

    private function handleFloat(): string
    {
        return 'float';
    }

    private function handleInteger(Field $field): string
    {
        return match (true) {
            $field->minValue >= 0 && $field->maxValue <= Integer::UNSIGNED_1_BYTE_MAX => 'tinyint unsigned',
            $field->minValue >= 0 && $field->maxValue <= Integer::UNSIGNED_2_BYTE_MAX => 'smallint unsigned',
            $field->minValue >= 0 && $field->maxValue <= Integer::UNSIGNED_3_BYTE_MAX => 'mediumint unsigned',
            $field->minValue >= 0 && $field->maxValue <= Integer::UNSIGNED_4_BYTE_MAX => 'int unsigned',
            $field->minValue >= 0 && $field->maxValue <= Integer::UNSIGNED_8_BYTE_MAX => 'bigint unsigned',

            $field->minValue >= Integer::SIGNED_1_BYTE_MAX && $field->maxValue <= Integer::SIGNED_1_BYTE_MAX => 'tinyint',
            $field->minValue >= Integer::SIGNED_2_BYTE_MAX && $field->maxValue <= Integer::SIGNED_2_BYTE_MAX => 'mediumint',
            $field->minValue >= Integer::SIGNED_3_BYTE_MAX && $field->maxValue <= Integer::SIGNED_3_BYTE_MAX => 'int',
            $field->minValue >= Integer::SIGNED_4_BYTE_MAX && $field->maxValue <= Integer::SIGNED_4_BYTE_MAX => 'bigint',

            default => throw new \Exception('out of bounds value range'),
        };
    }

    private function handleBoolean(): string
    {
        return 'tinyint unsigned';
    }
}
