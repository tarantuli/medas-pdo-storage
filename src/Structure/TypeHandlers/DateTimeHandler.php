<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\EntityManager\Types\DateTime;
use Medas\ServiceManager\Attributes\Service;

#[Service]
class DateTimeHandler extends BaseHandler
{
    public function fieldDefinition(Property $property): string
    {
        /** @var DateTime $type */
        return 'datetime';
    }

    public function deserialize(mixed $value): \DateTime|null
    {
        return $value === null ? null : new \DateTime($value);
    }

    /** @param \DateTime|null $value */
    public function serialize(mixed $value): string|null
    {
        return $value instanceof \DateTime ? $value->format('Y-m-d H:i:s') : $value;
    }
}
