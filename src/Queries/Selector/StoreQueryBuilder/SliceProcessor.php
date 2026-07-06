<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector\StoreQueryBuilder;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\Slice;
use Medas\PdoStorage\Exceptions\InvalidSliceOffset;

#[Service]
readonly class SliceProcessor
{
    public function process(Job $job, Slice|null $slice): void
    {
        if ($slice === null) {
            return;
        }

        if ($slice->from < 0) {
            throw new InvalidSliceOffset($slice->from);
        }

        // Standard SQL syntax supported by SQLite, MySQL, PostgreSQL, etc.
        $job->query .= ' limit ' . $slice->count . ' offset ' . $slice->from;
    }
}
