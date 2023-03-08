<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Mysql;

use Medas\PdoStorage\Drivers\Bases\BaseSerializer;
use Medas\ServiceManager\Interfaces\Type;

class Serializer extends BaseSerializer
{
    public function serialize(Type $type, mixed $value): mixed
    {
        if ($value instanceof \DateTime) {
            $value = $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        return parent::serialize($type, $value);
    }
}
