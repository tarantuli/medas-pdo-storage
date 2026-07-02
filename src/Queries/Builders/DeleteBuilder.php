<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\Slice;
use Medas\PdoStorage\{Events\QuoteIdentifierRequest, Queries\Query, Queries\QuerySet, Table};
use Medas\StorageManager\Interfaces\{Builders\DeleteBuilder as DeleteBuilderInterface, Store};
use Medas\StorageManager\UnitOfWork\{ActionSet, Priority};

#[Service]
readonly class DeleteBuilder implements DeleteBuilderInterface
{
    public function __construct(
        private Shared\ConditionAppender $conditionAppender,
        private Shared\SliceAppender     $sliceAppender,
        private Shared\SortAppender      $sortAppender,
    )
    {
    }

    public function build(
        Store      $store,
        array      $conditions,
        Priority   $priority = Priority::DeleteRecord,
        array      $sorts = [],
        Slice|null $slice = null
    ): ActionSet
    {
        /** @var Table $store->storage() */
        $arguments = [];
        $request = dispatch(new QuoteIdentifierRequest($store->storage(), $store->name()));
        $query = 'delete from ' . $request->quotedIdentifier . ' where ';

        $this->conditionAppender->append($store->storage(), $query, $arguments, $conditions);
        $this->sortAppender->append($store->storage(), $query, $sorts);
        $this->sliceAppender->append($query, $slice, false);

        return QuerySet::fromQuery(new Query($query, $arguments, $store->storage(), $priority));
    }
}
