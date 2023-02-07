<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\EntityManager\Attributes\HasId;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Values\Interfaces\Guid;

#[Service]
class ValueSerializer
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

        return $value;
    }
}
