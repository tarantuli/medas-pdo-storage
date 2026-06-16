<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Events;

use Medas\StorageManager\Interfaces\Storage;

class QuoteIdentifierRequest
{
    public string $quotedIdentifier;

    public function __construct(public readonly Storage $storage, public readonly string $identifier)
    {
    }
}
