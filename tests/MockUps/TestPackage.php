<?php

declare(strict_types=1);

namespace Medas\PdoStorageTest\MockUps;

use Medas\Core\{AsSingleton, BasePackage};
use Medas\ObjectInstantiator\ObjectInstantiatorPackage;
use Medas\PdoStorage\PdoStoragePackage;

class TestPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [
            PdoStoragePackage::instance(),
            ObjectInstantiatorPackage::instance(),
        ];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
