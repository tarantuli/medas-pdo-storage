<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Shared;

use Medas\Core\Attributes\{EventListener, Service};
use Medas\Json\JsonEncoder;
use Medas\StorageManager\Shared\SerializeValueRequest;

/**
 * PDO parameters can't be arrays, so any value the storage-manager already
 * serialized to an array (a #[DataHolder] value object) is JSON-encoded here,
 * at the pdo-storage boundary - keeping the "an array is stored as a JSON
 * varchar" detail out of the backend-agnostic layers.
 *
 * The reverse (JSON string -> array -> object) happens in the Hydrator's
 * ValueCaster, since the storage layer defers unserialization to it entirely.
 *
 * This runs after ValueSerializer (which produced the array) and only touches
 * values that are already arrays, so it never interferes with scalar arguments.
 */
#[Service]
readonly class ValueJsonEncoder
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
    )
    {
    }

    #[EventListener]
    public function handleSerializeRequest(SerializeValueRequest $request): void
    {
        if (is_array($request->serializedValue ?? null)) {
            $request->serializedValue = $this->jsonEncoder->encode($request->serializedValue);
        }
    }
}
