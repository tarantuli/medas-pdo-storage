<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\Exceptions\DriverNotImplemented;

#[Service]
class DriverHandlerManager
{
    /** @var DriverHandler[] */
    private array $driverHandlers = [];

    public function find(string $driverName): DriverHandler
    {
        foreach ($this->driverHandlers as $driverHandler) {
            if ($driverHandler->canHandle($driverName)) {
                return $driverHandler;
            }
        }

        throw new DriverNotImplemented($driverName);
    }

    public function addHandler(DriverHandler $handler): void
    {
        $this->driverHandlers[] = $handler;

        usort(
            $this->driverHandlers,
            fn(DriverHandler $a, DriverHandler $b) => -($a->priority() <=> $b->priority())
        );
    }
}
