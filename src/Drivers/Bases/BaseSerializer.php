<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\EntityManager\Attributes\HasId;
use Medas\EntityManager\Types\Type;
use Medas\ServiceManager\Values\Interfaces\Guid;
use Medas\StorageManager\Entities\TypeSerializer;

abstract class BaseSerializer implements TypeSerializer
{
    public function deserialize(Type $type, mixed $value): mixed
    {
        return $value;
    }

    public function serialize(Type $type, mixed $value): mixed
    {
        if ($value instanceof HasId) {
            return $value->id();
        }

        if ($value instanceof Guid) {
            return $value->toBytes();
        }

        return $value;
    }
}
