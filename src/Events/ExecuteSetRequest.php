<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Events;

use Medas\PdoStorage\Queries\QuerySet;

readonly class ExecuteSetRequest
{
    public function __construct(public readonly QuerySet $querySet)
    {
    }
}
