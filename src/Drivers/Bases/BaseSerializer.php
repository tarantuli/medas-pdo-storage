<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\EntityManager\Attributes\HasId;
use Medas\EntityManager\Types\{Boolean, Guid, Type};
use Medas\ServiceManager\Values\Interfaces\Guid as GuidValue;
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
        // Get the ID first, so other serializer can process its value
        if ($value instanceof HasId) {
            $value = $value->id();
        }

        if ($value instanceof GuidValue) {
            return $value->toBytes();
        }

        if ($type instanceof Boolean) {
            return (int) $value;
        }

        return $value;
    }
}
