<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\EntityManager\Selector\Selector;
use Medas\PdoStorage\Queries\QueryCollection;

interface SelectQueryBuilder
{
    public function build(Selector $selector, array $arguments): QueryCollection;
}
