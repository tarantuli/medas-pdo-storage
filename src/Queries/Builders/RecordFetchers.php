<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders;

use Medas\Core\Attributes\Service;
use Medas\StorageManager\Interfaces\{Fetchers, RecordFetchers as RecordFetchersInterface};

#[Service]
readonly class RecordFetchers implements RecordFetchersInterface
{
    public function __construct(
        private CollectionRecordFetcher $collectionRecordFetcher,
        private FilteredFetcher         $filteredFetcher,
    )
    {
    }

    public function filteredFetcher(): Fetchers\FilteredFetcher
    {
        return $this->filteredFetcher;
    }

    public function collectionRecordFetcher(): Fetchers\CollectionRecordFetcher
    {
        return $this->collectionRecordFetcher;
    }
}
