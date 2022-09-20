<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure\IdentifierQuoters;

interface IdentifierQuoter
{
    public function quote(string $identifier): string;
}
