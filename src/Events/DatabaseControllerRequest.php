<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Events;

use Medas\PdoStorage\{Database, DatabaseController};

class DatabaseControllerRequest
{
    public DatabaseController $databaseController;

    public function __construct(
        public Database $database,
    )
    {
    }
}
