<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\Core\Collections\GenericCollection;
use Medas\StorageManager\UnitOfWork\ActionCollection;

/** @extends GenericCollection<Query> */
class QueryCollection extends GenericCollection implements ActionCollection
{
    public function current(): Query
    {
        return parent::current();
    }
}
