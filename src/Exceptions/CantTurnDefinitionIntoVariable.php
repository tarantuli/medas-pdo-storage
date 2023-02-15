<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;

class CantTurnDefinitionIntoVariable extends BaseException
{
    public function __construct(string $definition)
    {
        parent::__construct($definition);
    }

    public function pattern(): string
    {
        return 'Cannot turn definition "%s" into a variable';
    }
}
