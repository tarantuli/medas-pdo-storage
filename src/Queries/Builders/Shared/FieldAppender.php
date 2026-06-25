<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders\Shared;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\{Database, Events\DatabaseControllerRequest};

#[Service]
readonly class FieldAppender
{
    public function append(Database $database, string &$query, array &$arguments, array $fields): void
    {
        $request = dispatch(new DatabaseControllerRequest($database));
        $driverHandler = $request->databaseController->driverHandler;

        foreach ($fields as $field => $value) {
            $query .= $driverHandler->quote($database, $field) . ' = ?, ';
            $arguments[] = $value;
        }

        $query = substr($query, 0, -2);
    }
}
