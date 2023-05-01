<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\Drivers\DriverHandler;
use Medas\PdoStorage\Exceptions\DriverNotImplemented;

#[Service]
class DriverHandlerManager
{
    /** @var DriverHandler[] */
    private array $handlers = [];

    public function find(string $driverName): DriverHandler
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($driverName)) {
                return $handler;
            }
        }

        throw new DriverNotImplemented($driverName);
    }

    public function add(DriverHandler $handler): void
    {
        $this->handlers[] = $handler;

        usort($this->handlers,
            fn(DriverHandler $a, DriverHandler $b) => -($a->priority() <=> $b->priority())
        );
    }
}
