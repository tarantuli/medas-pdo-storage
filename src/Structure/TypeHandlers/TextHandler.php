<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\EntityManager\Types\{Integer, Text};
use Medas\ServiceManager\Attributes\Service;

#[Service]
class TextHandler extends BaseHandler
{
    public function fieldDefinition(Property $property): string
    {
        /** @var Text $type */
        $type = $property->type;

        /** @noinspection PhpDuplicateMatchArmBodyInspection */
        return match (true) {
            $type->maxLength <= Integer::UNSIGNED_1_BYTE_MAX => sprintf('varchar(%s)', $type->maxLength),
            $type->maxLength <= Integer::UNSIGNED_2_BYTE_MAX => 'text',
            $type->maxLength <= Integer::UNSIGNED_3_BYTE_MAX => 'mediumtext',
            $type->maxLength <= Integer::UNSIGNED_4_BYTE_MAX => 'longtext',
            default => 'text'
        };
    }
}
