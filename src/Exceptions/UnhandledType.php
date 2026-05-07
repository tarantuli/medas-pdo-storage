<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\StorageManager\Type;

class UnhandledType extends BaseException
{
    public function __construct(Type $type)
    {
        parent::__construct($type);
    }

    public function pattern(): string
    {
        return 'unhandled type %s';
    }
}
