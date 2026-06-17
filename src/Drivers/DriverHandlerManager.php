<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers;

use Medas\Core\{Attributes\Service, CachedImplementorList, Lists\SortByPriority};
use Medas\PdoStorage\Exceptions\DriverNotImplemented;

#[Service]
readonly class DriverHandlerManager
{
    private CachedImplementorList $driverHandlers;

    public function __construct()
    {
        $this->driverHandlers = new CachedImplementorList(
            DriverHandler::class,
            SortByPriority::HighToLow
        );
    }

    public function find(string $driverName): DriverHandler
    {
        foreach ($this->driverHandlers->get() as $driverHandler) {
            /** @var DriverHandler $driverHandler */
            if ($driverHandler->canHandle($driverName)) {
                return $driverHandler;
            }
        }

        throw new DriverNotImplemented($driverName);
    }
}
