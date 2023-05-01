<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\Drivers\Handler;
use Medas\PdoStorage\Drivers\HandlerManager;
use Medas\PdoStorage\Exceptions\DriverNotImplemented;

#[Service]
class DriverHandlerManager
{
    /** @var HandlerManager[] */
    private array $handlerManagers = [];

    public function find(string $driverName, DatabaseController $controller): Handler
    {
        foreach ($this->handlerManagers as $handler) {
            if ($handler->canHandle($driverName)) {
                return $handler->initialize($controller);
            }
        }

        throw new DriverNotImplemented($driverName);
    }

    public function addManager(HandlerManager $manager): void
    {
        $this->handlerManagers[] = $manager;

        usort($this->handlerManagers,
            fn(HandlerManager $a, HandlerManager $b) => -($a->priority() <=> $b->priority())
        );
    }
}
