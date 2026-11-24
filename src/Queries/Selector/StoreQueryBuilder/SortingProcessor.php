<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector\StoreQueryBuilder;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\Sorting\{SortBy, SortDirection};

#[Service]
readonly class SortingProcessor
{
    public const array SORTING_DIRECTIONS = [
        SortDirection::ASC->name => 'asc',
        SortDirection::DESC->name => 'desc',
    ];

    public function __construct(
        private CalculationsProcessor $calculationsProcessor,
    )
    {
    }

    /** @param SortBy[] $sorts */
    public function process(Job $job, array $sorts): void
    {
        $parts = array_map(
            fn(SortBy $sort) => $this->calculationsProcessor->sortPart($job, $sort),
            $sorts
        );

        if ($parts) {
            $job->query .= ' order by ' . implode(', ', $parts);
        }
    }
}
