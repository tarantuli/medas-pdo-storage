<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector\StoreQueryBuilder;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\Grouping\GroupBy;

#[Service]
readonly class GroupingProcessor
{
    /** @param GroupBy[] $groupings */
    public function process(Job $job, array $groupings): void
    {
        $parts = [];

        foreach ($groupings as $grouping) {
            if (in_array($grouping->property->name, $job->aliases, true)) {
                $parts[] = $job->driverHandler->quote($job->database, $grouping->property->name);
            }
            else {
                $parts[] = $job->storeForProperty($grouping->property->entity, $grouping->property->name)
                    . '.'
                    . $job->driverHandler->quote($job->database, $grouping->property->name);
            }
        }

        if ($parts) {
            $job->query .= ' group by ' . implode(', ', $parts);
        }
    }
}
