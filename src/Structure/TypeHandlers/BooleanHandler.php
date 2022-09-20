<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\ServiceManager\Attributes\Service;

#[Service]
class BooleanHandler extends BaseHandler
{
    public function fieldDefinition(Property $property): string
    {
        return 'tinyint unsigned';
    }
}
