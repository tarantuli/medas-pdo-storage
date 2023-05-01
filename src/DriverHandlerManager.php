<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{CacheManager, ImplementorFinder};
use Medas\PdoStorage\Drivers\DriverHandler;
use Medas\PdoStorage\Exceptions\DriverNotImplemented;

#[Service]
class DriverHandlerManager
{
    public function __construct(
        private readonly CacheManager $cacheManager,
    )
    {
    }

    public function find(string $driverName): DriverHandler
    {
        $handlers = $this->cacheManager->get()->get(self::class, fn() => $this->gatherHandlers());

        foreach ($handlers as $handler) {
            if ($handler->canHandle($driverName)) {
                return $handler;
            }
        }

        throw new DriverNotImplemented($driverName);
    }

    /** @return DriverHandler[] $handler */
    private function gatherHandlers(): array
    {
        $handlers = service(ImplementorFinder::class)->find(DriverHandler::class);

        usort($handlers,
            fn(DriverHandler $a, DriverHandler $b) => -($a->priority() <=> $b->priority())
        );

        return $handlers;
    }
}
