<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\EntityManager\Types\{Boolean, Guid};
use Medas\PdoStorage\ValueSerializer;
use Medas\ServiceManager\Interfaces\{GuidProvider, Type};
use Medas\StorageManager\Entities\TypeSerializer;

abstract class BaseSerializer implements TypeSerializer
{
    public function deserialize(Type $type, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

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
