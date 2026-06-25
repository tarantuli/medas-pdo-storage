<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders\Shared;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\{
    Exceptions\UnhandledSortType,
    Operants\Property,
    Operants\Value,
    Sorting\SortBy
};
use Medas\PdoStorage\Database;
use Medas\PdoStorage\Events\DatabaseControllerRequest;
use Medas\PdoStorage\Exceptions\UntrustedValueUsedInSorting;
use Medas\PdoStorage\Queries\Selector\StoreQueryBuilder\SortingProcessor;

#[Service]
readonly class SortAppender
{
    public function append(
        Database $database,
        string   &$query,
        array    $sorts,
    ): void
    {
        $request = dispatch(new DatabaseControllerRequest($database));
        $driverHandler = $request->databaseController->driverHandler;
        $parts = [];

        foreach ($sorts as $sort) {
            if ($sort instanceof SortBy && $sort->operant instanceof Property) {
                $parts[] = $driverHandler->quote($database, $sort->operant->name)
                    . ' '
                    . SortingProcessor::SORTING_DIRECTIONS[$sort->direction->name];

                continue;
            }

            if ($sort instanceof SortBy && $sort->operant instanceof Value) {
                if (!$sort->operant->isTrusted) {
                    throw new UntrustedValueUsedInSorting($sort->operant);
                }

                $parts[] = $sort->operant->value
                    . ' '
                    . SortingProcessor::SORTING_DIRECTIONS[$sort->direction->name];

                continue;
            }

            throw new UnhandledSortType($sort);
        }

        if ($parts) {
            $query .= ' order by ' . implode(', ', $parts);
        }
    }
}
