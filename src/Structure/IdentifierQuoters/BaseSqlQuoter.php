<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure\IdentifierQuoters;

class BaseSqlQuoter implements IdentifierQuoter
{
    public function quote(string $identifier): string
    {
        return '"' . $identifier . '"';
    }
}
