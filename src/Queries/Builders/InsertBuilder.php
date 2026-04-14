<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\{PdoStorageController, Queries\Query, Queries\QuerySet};
use Medas\StorageManager\Interfaces\{Builders\InsertBuilder as InsertBuilderInterface, Store};
use Medas\StorageManager\UnitOfWork\{ActionSet, Priority};

#[Service]
readonly class InsertBuilder implements InsertBuilderInterface
{
    public function __construct(
        private PdoStorageController $pdoStorageController,
    )
    {
    }

    public function build(Store $store, array $values, Priority $priority = Priority::CreateRecord): ActionSet
    {
        $names = [];
        $arguments = [];

        foreach ($values as $field => $value) {
            $names[] = $this->pdoStorageController->quote($store->storage(), $field);
            $arguments[] = $value;
        }

        $query = 'insert into '
            . $this->pdoStorageController->quote($store->storage(), $store->name())
            . ' ('
            . implode(', ', $names)
            . ')'
            . ' values ('
            . implode(', ', array_fill(0, count($names), '?'))
            . ')';

        return QuerySet::fromQuery(new Query($query, $arguments, $store->storage(), $priority));
    }
}
