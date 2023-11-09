<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\EntityManager\Selector\Parameter;
use Medas\PdoStorage\Database;

class ParameterizedQuery
{
    public function __construct(
        public string                                    $query,
        /** @var Parameter[] $parameters */ public array $parameters,
        public array                                     $constants,
        public Database                                  $database,
    )
    {
    }
}
