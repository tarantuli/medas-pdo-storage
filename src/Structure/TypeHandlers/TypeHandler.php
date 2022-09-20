<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\PdoStorage\Structure\Blueprint\ForeignKey;
use Medas\StorageManager\Entities\TypeSerializer;

interface TypeHandler extends TypeSerializer
{
    public function fieldDefinition(Property $property): string;

    public function foreignKey(Property $property): ForeignKey|null;
}
