<?php

declare(strict_types=1);

namespace Medas\PdoStorageTest\MockUps;

use Medas\PdoStorage\Database;

readonly class MockUpDatabase extends Database
{
    public function __construct()
    {
        parent::__construct('', '', '', 'name', false);
    }
}
