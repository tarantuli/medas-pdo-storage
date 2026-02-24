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

    private bool $isSorted = false;

    public function find(string $driverName): DriverHandler
    {
        if (!$this->isSorted) {
            usort(
                $this->driverHandlers,
                fn(DriverHandler $a, DriverHandler $b) => -($a->priority() <=> $b->priority())
            );

            $this->isSorted = true;
        }

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
        $this->isSorted = false;
    }
}
