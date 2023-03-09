<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\EntityManager\Types\{Boolean, Guid as GuidType};
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Interfaces\{Guid, GuidProvider, HasId, Serializer, Type};

#[Service]
class ValueSerializer implements Serializer
{
    public function serialize(mixed $value): mixed
    {
        // Get the ID first, so other serializers can process its value
        if ($value instanceof HasId) {
            $value = $value->id();
        }

        if ($value instanceof Guid) {
            return $value->toBytes();
        }

        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if (is_bool($value)) {
            return (string) (int) $value;
        }

        return $value;
    }

    public function unserialize(mixed $value, Type $type = null): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($type instanceof GuidType) {
            return service(GuidProvider::class)->fromBytes($value);
        }

        if ($type instanceof Boolean) {
            return (bool) $value;
        }

        return $value;
    }
}
