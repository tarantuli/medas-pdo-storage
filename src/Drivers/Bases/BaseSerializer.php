<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\ValueSerializer;
use Medas\ServiceManager\Interfaces\{Serializer, Type};

abstract class BaseSerializer implements Serializer
{
    private readonly ValueSerializer $valueSerializer;

    public function __construct()
    {
        $this->valueSerializer = service(ValueSerializer::class);
    }

    public function serialize(mixed $value): mixed
    {
        return $this->valueSerializer->serialize($value);
    }

    public function unserialize(Type $type, mixed $value): mixed
    {
        return $this->valueSerializer->unserialize($type, $value);
    }
}
