<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders\StoreQueryBuilder;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\Pagination;
use Medas\PdoStorage\Exceptions\InvalidPaginationPage;

#[Service]
readonly class PaginationProcessor
{
    public function process(Job $job, Pagination|null $pagination): void
    {
        if ($pagination === null) {
            return;
        }

        if ($pagination->page <= 0) {
            throw new InvalidPaginationPage($pagination->page);
        }

        $limit = $pagination->perPage;
        $offset = ($pagination->page - 1) * $pagination->perPage;

        // Standard SQL syntax supported by SQLite, MySQL, PostgreSQL, etc.
        $job->query .= ' limit ' . $limit . ' offset ' . $offset;
    }
}
