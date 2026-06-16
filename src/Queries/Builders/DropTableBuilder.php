<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\Drivers\Interfaces\DeleteStoreBuilder;
use Medas\PdoStorage\Events\QuoteIdentifierRequest;
use Medas\PdoStorage\Queries\{Query, QuerySet};
use Medas\StorageManager\{Interfaces\Store, UnitOfWork\ActionSet};

#[Service]
readonly class DropTableBuilder implements DeleteStoreBuilder
{
    public function build(Store $store): ActionSet
    {
        $request = dispatch(new QuoteIdentifierRequest($store->storage(), $store->name()));

        $query = new Query(
            'drop table if exists ' . $request->quotedIdentifier,
            [],
            $store->storage(),
        );

        return QuerySet::fromQuery($query);
    }
}
