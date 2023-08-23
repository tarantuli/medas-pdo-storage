<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\Core\Collections\GenericCollection;
use Medas\StorageManager\Interfaces\RecordSet;
use Medas\StorageManager\UnitOfWork\ActionSet;

/** @extends GenericCollection<Query> */
class QuerySet extends GenericCollection implements ActionSet
{
    private RecordSet|null $lastRecordSet = null;

    public static function fromQuery(Query $query): QuerySet
    {
        return new self([$query]);
    }

    public function current(): Query
    {
        return parent::current();
    }

    public function recordSet(): RecordSet
    {
        return $this->lastRecordSet;
    }
}
