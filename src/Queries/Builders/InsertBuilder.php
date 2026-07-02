<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\{Database, Events\QuoteIdentifierRequest, Queries\Query, Queries\QuerySet};
use Medas\StorageManager\Interfaces\{Builders\InsertBuilder as InsertBuilderInterface, Store};
use Medas\StorageManager\UnitOfWork\{ActionSet, Priority};

#[Service]
readonly class InsertBuilder implements InsertBuilderInterface
{
    public function build(Store $store, array $values, Priority $priority = Priority::CreateRecord): ActionSet
    {
        /** @var Database $storage */
        $storage = $store->storage();
        $names = [];
        $arguments = [];

        foreach ($values as $field => $value) {
            $request = dispatch(new QuoteIdentifierRequest($storage, $field));
            $names[] = $request->quotedIdentifier;
            $arguments[] = $value;
        }

        $request = dispatch(new QuoteIdentifierRequest($storage, $store->name()));

        $query = 'insert into '
            . $request->quotedIdentifier
            . ' ('
            . implode(', ', $names)
            . ')'
            . ' values ('
            . implode(', ', array_fill(0, count($names), '?'))
            . ')';

        return QuerySet::fromQuery(new Query($query, $arguments, $storage, $priority));
    }
}
