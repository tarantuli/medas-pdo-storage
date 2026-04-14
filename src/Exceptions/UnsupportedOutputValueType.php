<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\EntityManager\Selector\OutputValues\OutputValue;

class UnsupportedOutputValueType extends BaseException
{
    public function __construct(OutputValue $value)
    {
        parent::__construct($value::class);
    }

    public function pattern(): string
    {
        return 'Unsupported output value type %s';
    }
}
