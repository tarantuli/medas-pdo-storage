<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders\StoreQueryBuilder;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\{
    Exceptions\UnhandledSortType,
    Operants\Property,
    Operants\Value,
    Sorting\SortBy,
    Sorting\SortDirection
};
use Medas\PdoStorage\Exceptions\UntrustedValueUsedInSorting;

#[Service]
readonly class SortingProcessor
{
    public const array SORTING_DIRECTIONS = [
        SortDirection::ASC->name => 'asc',
        SortDirection::DESC->name => 'desc',
    ];

    /** @param SortBy[] $sorts */
    public function process(Job $job, array $sorts): void
    {
        $parts = [];

        foreach ($sorts as $sort) {
            if ($sort instanceof SortBy && $sort->operant instanceof Property) {
                $parts[] = $job->stores[$sort->operant->entity ?? $job->mainEntity]
                    . '.'
                    . $job->driverHandler->quote($job->database, $sort->operant->name)
                    . ' '
                    . self::SORTING_DIRECTIONS[$sort->direction->name];

                continue;
            }

            if ($sort instanceof SortBy && $sort->operant instanceof Value) {
                if (!$sort->operant->isTrusted) {
                    throw new UntrustedValueUsedInSorting($sort->operant);
                }

                $parts[] = $sort->operant->value
                    . ' '
                    . self::SORTING_DIRECTIONS[$sort->direction->name];

                continue;
            }

            throw new UnhandledSortType($sort);
        }

        if ($parts) {
            $job->query .= ' order by ' . implode(', ', $parts);
        }
    }
}
