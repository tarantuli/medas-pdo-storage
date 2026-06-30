<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\Slice;
use Medas\PdoStorage\{Events\QuoteIdentifierRequest, Queries\Query, Queries\QuerySet};
use Medas\StorageManager\Interfaces\{Builders\UpdateBuilder as UpdateBuilderInterface, Store};
use Medas\StorageManager\UnitOfWork\{ActionSet, Priority};

#[Service]
readonly class UpdateBuilder implements UpdateBuilderInterface
{
    public function __construct(
        private Shared\ConditionAppender $conditionAppender,
        private Shared\FieldAppender     $fieldAppender,
        private Shared\SliceAppender     $sliceAppender,
        private Shared\SortAppender      $sortAppender,
    )
    {
    }

    public function build(
        Store      $store,
        array      $updates,
        array      $conditions,
        array      $sorts = [],
        Slice|null $slice = null
    ): ActionSet
    {
        $arguments = [];
        $request = dispatch(new QuoteIdentifierRequest($store->storage(), $store->name()));
        $query = 'update ' . $request->quotedIdentifier . ' set ';

        $this->fieldAppender->append($store->storage(), $query, $arguments, $updates);

        $query .= ' where ';

        $this->conditionAppender->append($store->storage(), $query, $arguments, $conditions);
        $this->sortAppender->append($store->storage(), $query, $sorts);
        $this->sliceAppender->append($query, $slice, false);

        return QuerySet::fromQuery(new Query($query, $arguments, $store->storage(), Priority::UpdateRecord));
    }
}
