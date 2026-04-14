<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\{Database, PdoStorageController};

#[Service]
readonly class FieldAppender
{
    public function __construct(
        private PdoStorageController $pdoStorageController,
    )
    {
    }

    public function append(Database $database, string &$query, array &$arguments, array $fields): void
    {
        $driverHandler = $this->pdoStorageController->getDatabaseController($database)->driverHandler;

        foreach ($fields as $field => $value) {
            $query .= $driverHandler->quote($database, $field) . ' = ?, ';
            $arguments[] = $value;
        }

        $query = substr($query, 0, -2);
    }
}
