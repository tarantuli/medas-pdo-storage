<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\Drivers\Interfaces\DeleteStoreBuilder;
use Medas\PdoStorage\PdoStorageController;
use Medas\PdoStorage\Queries\{Query, QuerySet};
use Medas\StorageManager\{Interfaces\Store, UnitOfWork\ActionSet};

#[Service]
readonly class DropTableBuilder implements DeleteStoreBuilder
{
    public function __construct(
        private PdoStorageController $pdoStorageController,
    )
    {
    }

    public function build(Store $store): ActionSet
    {
        $query = new Query(
            'drop table if exists '
                . $this->pdoStorageController->quote($store->storage(), $store->name()),
            [],
            $store->storage(),
        );

        return QuerySet::fromQuery($query);
    }
}
