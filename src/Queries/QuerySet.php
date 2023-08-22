<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\Core\Collections\GenericCollection;
use Medas\StorageManager\Interfaces\RecordSet;
use Medas\StorageManager\UnitOfWork\ActionSet;

/** @extends GenericCollection<Query> */
class QuerySet extends GenericCollection implements ActionSet
{
    public static function fromQuery(Query $query): QuerySet
    {
        return new self([$query]);
    }

    public function current(): Query
    {
        return parent::current();
    }

    private RecordSet|null $lastRecordSet = null;

    public function execute(): void
    {
        foreach ($this->data as $query) {
            $query->execute();
            $this->lastRecordSet = $query->recordSet();
        }
    }

    public function recordSet(): RecordSet
    {
        return $this->lastRecordSet;
    }
}
