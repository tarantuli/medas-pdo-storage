<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Drivers\Driver;
use Medas\PdoStorage\Drivers\Interfaces\FieldHandler;

abstract class BaseFieldHandler implements FieldHandler
{
    public function __construct(
        protected readonly Driver $driver,
    )
    {
    }
}
