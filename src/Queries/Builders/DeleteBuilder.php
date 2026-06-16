<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\{Events\QuoteIdentifierRequest, Queries\Query, Queries\QuerySet};
use Medas\StorageManager\Interfaces\{Builders\DeleteBuilder as DeleteBuilderInterface, Store};
use Medas\StorageManager\UnitOfWork\{ActionSet, Priority};

#[Service]
readonly class DeleteBuilder implements DeleteBuilderInterface
{
    public function __construct(
        private ConditionAppender $conditionAppender,
    )
    {
    }

    public function build(Store $store, array $conditions, Priority $priority = Priority::DeleteRecord): ActionSet
    {
        $arguments = [];
        $request = dispatch(new QuoteIdentifierRequest($store->storage(), $store->name()));
        $query = 'delete from ' . $request->quotedIdentifier . ' where ';

        $this->conditionAppender->append($store->storage(), $query, $arguments, $conditions);

        return QuerySet::fromQuery(new Query($query, $arguments, $store->storage(), $priority));
    }
}
