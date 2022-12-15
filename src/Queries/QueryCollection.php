<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\Core\Collections\BaseArrayCollection;
use Medas\StorageManager\UnitOfWork\ActionCollection;

class QueryCollection extends BaseArrayCollection implements ActionCollection
{
    public function current(): Query
    {
        return parent::current();
    }
}
