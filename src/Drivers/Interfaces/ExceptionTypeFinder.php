<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\Exceptions\ExceptionType;

interface ExceptionTypeFinder
{
    public function find(\Exception|\Error $e): ExceptionType;
}
