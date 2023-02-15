<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\PdoStorage\Queries\Query;

class PdoDatabase extends BaseException
{
    public function __construct(string $message, Query $query)
    {
        parent::__construct($message, $query->query, $query->arguments);
    }

    public function pattern(): string
    {
        return 'error %s when executing %s with arguments %s';
    }
}
