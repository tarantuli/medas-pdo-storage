<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\Core\Exceptions\StorageExceptionType;

interface ExceptionTypeFinder
{
    public function find(\Exception|\Error $e): StorageExceptionType;
}
