<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\Core\Collections\GenericCollection;
use Medas\StorageManager\UnitOfWork\ActionSet;

/** @extends GenericCollection<Query> */
class QuerySet extends ActionSet
{
    public static function fromQuery(Query $query): QuerySet
    {
        return new self([$query]);
    }
}
