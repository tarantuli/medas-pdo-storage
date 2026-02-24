<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;

class StatementIsNotSet extends BaseException
{
    public function pattern(): string
    {
        return 'Statement is not set. The query probably hasn\'t been executed yet';
    }
}
