<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\EntityManager\Selector\Operants\Value;

class UntrustedValueUsedInSorting extends BaseException
{
    public function __construct(Value $value)
    {
        parent::__construct($value->value);
    }

    public function pattern(): string
    {
        return 'Value used in sorting was not marked as trusted: %s';
    }
}
