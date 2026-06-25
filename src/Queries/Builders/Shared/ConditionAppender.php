<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders\Shared;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Filters\{Between, LessThan, MoreThan};
use Medas\PdoStorage\{Database, Events\DatabaseControllerRequest};

#[Service]
readonly class ConditionAppender
{
    public function append(
        Database $database,
        string   &$query,
        array    &$arguments,
        array    $filters,
        string   $separator = 'and',
    ): void
    {
        $request = dispatch(new DatabaseControllerRequest($database));
        $driverHandler = $request->databaseController->driverHandler;

        foreach ($filters as $field => $value) {
            if ($value instanceof LessThan) {
                $query .= $driverHandler->quote($database, $value->field)
                    . ' < ? '
                    . $separator
                    . ' ';

                $arguments[] = $value->value;
            }
            elseif ($value instanceof MoreThan) {
                $query .= $driverHandler->quote($database, $value->field)
                    . ' > ? '
                    . $separator
                    . ' ';

                $arguments[] = $value->value;
            }
            elseif ($value instanceof Between) {
                $query .= $driverHandler->quote($database, $value->field)
                    . ' between ? and ? '
                    . $separator
                    . ' ';

                $arguments[] = $value->lowerValue;
                $arguments[] = $value->upperValue;
            }
            else {
                if ($value === null) {
                    $query .= $driverHandler->quote($database, $field)
                        . ' is null '
                        . $separator
                        . ' ';
                }
                else {
                    $query .= $driverHandler->quote($database, $field) . ' = ? ' . $separator . ' ';
                    $arguments[] = $value;
                }
            }
        }

        if ($filters) {
            $query = substr($query, 0, -2 - strlen($separator));
        }
        else {
            $query .= '1=1';
        }
    }
}
