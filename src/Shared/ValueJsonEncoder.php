<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Shared;

use Medas\Core\Attributes\{EventListener, Service};
use Medas\EntityManager\Events\NormalizeStorageValueRequest;
use Medas\Json\JsonEncoder;
use Medas\StorageManager\Shared\SerializeValueRequest;

/**
 * Bridges #[DataHolder] value objects to pdo-storage's string-only columns, on
 * both legs of the round-trip - so the "an array is stored as a JSON varchar"
 * detail lives entirely within pdo-storage and never leaks into the
 * backend-agnostic layers:
 *
 * - On write, a value the storage-manager already serialized to an array is
 *   JSON-encoded here (PDO parameters can't be arrays).
 * - On read, the Hydrator's ValueCaster dispatches a NormalizeStorageValueRequest;
 *   this decodes the JSON varchar back to an array for it to cast to the object.
 *
 * The write leg runs after ValueSerializer (which produced the array). The read
 * leg only fires for DataHolder values (ValueCaster dispatches it nowhere else),
 * so decoding any string it sees is safe.
 */
#[Service]
readonly class ValueJsonEncoder
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
    )
    {
    }

    #[EventListener(priority: -100)]
    public function handleSerializeRequest(SerializeValueRequest $request): void
    {
        if (is_array($request->serializedValue ?? null)) {
            $request->serializedValue = $this->jsonEncoder->encode($request->serializedValue);
        }
    }

    #[EventListener]
    public function handleNormalizeRequest(NormalizeStorageValueRequest $request): void
    {
        if (is_string($request->value)) {
            $request->normalizedValue = $this->jsonEncoder->decode($request->value);
        }
    }
}
