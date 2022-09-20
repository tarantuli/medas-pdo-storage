<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\EntityManager\Types\{Binary, Integer};
use Medas\ServiceManager\Attributes\Service;

#[Service]
class BinaryHandler extends BaseHandler
{
    public function fieldDefinition(Property $property): string
    {
        /** @var Binary $type */
        $type = $property->type;

        /** @noinspection PhpDuplicateMatchArmBodyInspection */
        return match (true) {
            $type->maxLength <= Integer::UNSIGNED_1_BYTE_MAX => sprintf('varbinary(%s)', $type->maxLength),
            $type->maxLength <= Integer::UNSIGNED_2_BYTE_MAX => 'blob',
            $type->maxLength <= Integer::UNSIGNED_3_BYTE_MAX => 'mediumblob',
            $type->maxLength <= Integer::UNSIGNED_4_BYTE_MAX => 'longblob',
            default => 'blob'
        };
    }
}
