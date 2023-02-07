<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\EntityManager\Types\{Boolean, Guid, Type};
use Medas\PdoStorage\ValueSerializer;
use Medas\ServiceManager\Values\Interfaces\GuidProvider;
use Medas\StorageManager\Entities\TypeSerializer;

abstract class BaseSerializer implements TypeSerializer
{
    public function deserialize(Type $type, mixed $value): mixed
    {
        if ($type instanceof Guid) {
            $value = service(GuidProvider::class)->fromBytes($value);
        }

        return $value;
    }

    public function serialize(Type $type, mixed $value): mixed
    {
        $value = service(ValueSerializer::class)->serialize($value);

        if ($type instanceof Boolean) {
            return (int) $value;
        }

        return $value;
    }
}
