<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;

class OffsetIsNotAllowed extends BaseException
{
    public function pattern(): string
    {
        return 'Offset is not allowed for this query';
    }
}
