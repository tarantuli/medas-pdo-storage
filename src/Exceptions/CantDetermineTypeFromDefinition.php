<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;

class CantDetermineTypeFromDefinition extends BaseException
{
    public function __construct(string $remainder, string $definition)
    {
        parent::__construct($remainder, $definition);
    }

    public function pattern(): string
    {
        return 'Cannot determine type from remainder "%s" from definition "%s"';
    }
}
