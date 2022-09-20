<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\EntityManager\Types\Integer;
use Medas\ServiceManager\Attributes\Service;

#[Service]
class IntegerHandler extends BaseHandler
{
    public function fieldDefinition(Property $property): string
    {
        /** @var Integer $type */
        $type = $property->type;

        return match (true) {
            $type->minValue >= 0 && $type->maxValue <= Integer::UNSIGNED_1_BYTE_MAX => 'tinyint unsigned',
            default => 'int unsigned'
        };
    }
}
