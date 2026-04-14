<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\{PdoStorageController, Queries\Query, Queries\QuerySet};
use Medas\StorageManager\Interfaces\Builders\GetBuilder as GetBuilderInterface;
use Medas\StorageManager\UnitOfWork\ActionSet;

#[Service]
readonly class GetBuilder implements GetBuilderInterface
{
    public function __construct(
        private ConditionAppender    $conditionAppender,
        private PdoStorageController $pdoStorageController,
    )
    {
    }

    public function build(array $stores, array $filters): ActionSet
    {
        $database = $stores[0]->storage();
        $arguments = [];
        $query = 'select * from ';

        foreach ($stores as $store) {
            $query .= $this->pdoStorageController->quote($database, $store->name()) . ', ';
        }

        $query = substr($query, 0, -2);

        if ($filters) {
            $query .= ' where ';

            $this->conditionAppender->append($database, $query, $arguments, $filters);
        }

        return QuerySet::fromQuery(new Query($query, $arguments, $database));
    }
}
