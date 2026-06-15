<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

use Medas\Core\{Attributes\Service, Interfaces\CacheManager, Interfaces\ImplementorFinder};
use Medas\PdoStorage\Exceptions\DriverNotImplemented;

#[Service]
readonly class DriverHandlerManager
{
    /** @var DriverHandler[] */
    private array $driverHandlers;

    public function __construct(
        private CacheManager      $cacheManager,
        private ImplementorFinder $implementorFinder,
    )
    {
        $this->driverHandlers = namesToServices($this->cacheManager->get()->get(
            __CLASS__,
            fn() => $this->discoverHandlers()
        ));
    }

    /** @return class-string[] */
    private function discoverHandlers(): array
    {
        $driverHandlers = namesToServices($this->implementorFinder->find(DriverHandler::class));

        usort(
            $driverHandlers,
            fn(DriverHandler $a, DriverHandler $b) => -($a->priority() <=> $b->priority())
        );

        return servicesToNames($driverHandlers);
    }

    public function find(string $driverName): DriverHandler
    {
        foreach ($this->driverHandlers as $driverHandler) {
            if ($driverHandler->canHandle($driverName)) {
                return $driverHandler;
            }
        }

        throw new DriverNotImplemented($driverName);
    }
}
