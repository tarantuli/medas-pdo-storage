<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;

class DriverNotImplementedException extends BaseException
{
    public function __construct(string $driverName)
    {
        parent::__construct($driverName);
    }

    public function pattern(): string
    {
        return 'pdo driver %s is not implemented';
    }
}
