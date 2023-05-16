<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\Core\Interfaces\{Serializer, Type};
use Medas\PdoStorage\ValueSerializer;

abstract readonly class BaseSerializer implements Serializer
{
    private ValueSerializer $valueSerializer;

    public function __construct()
    {
        $this->valueSerializer = service(ValueSerializer::class);
    }

    public function serialize(mixed $value): mixed
    {
        return $this->valueSerializer->serialize($value);
    }

    public function unserialize(mixed $value, Type $type = null): mixed
    {
        return $this->valueSerializer->unserialize($value, $type);
    }
}
