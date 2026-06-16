<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Events;

use Medas\StorageManager\Interfaces\{Storage, Store};

class GetStoreRequest
{
    public Store $store;

    public function __construct(public readonly Storage $storage, public readonly string $name)
    {
    }
}
