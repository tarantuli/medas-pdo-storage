<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure\IdentifierQuoters;

class MysqlQuoter implements IdentifierQuoter
{
    public function quote(string $identifier): string
    {
        return '`' . $identifier . '`';
    }
}
