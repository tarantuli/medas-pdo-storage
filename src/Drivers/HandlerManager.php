<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

use Medas\PdoStorage\DatabaseController;

interface HandlerManager
{
    public function canHandle(string $driverName): bool;

    public function priority(): int;

    public function initialize(DatabaseController $controller): Handler;
}
