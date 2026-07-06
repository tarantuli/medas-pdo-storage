<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;

class InvalidSliceOffset extends BaseException
{
    public function __construct(int $offset)
    {
        parent::__construct($offset);
    }

    public function pattern(): string
    {
        return 'invalid slice offset %s';
    }
}
