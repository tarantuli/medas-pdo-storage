<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    Interfaces\ManagedCollection,
    Types\Collection
};
use Medas\PdoStorage\ConfigOptions\JoinTables\TableNamingStrategy;
use Medas\PdoStorage\Events\GetStoreRequest;
use Medas\PdoStorage\JoinTables\NamingStrategy;
use Medas\PdoStorage\Queries\QuerySet;
use Medas\StorageManager\Interfaces\{
    Builders\CollectionUpdateBuilder as CollectionUpdateBuilderInterface,
    Store
};
use Medas\StorageManager\UnitOfWork\{ActionSet, Priority};

#[Service]
readonly class CollectionUpdateBuilder implements CollectionUpdateBuilderInterface
{
    public function __construct(
        private DeleteBuilder  $deleteBuilder,
        private InsertBuilder  $insertBuilder,

        #[ConfigValue(TableNamingStrategy::class)]
        private NamingStrategy $namingStrategy,
        private UpdateBuilder  $updateBuilder,
    )
    {
    }

    public function build(
        Store             $store,
        object            $entity,
        string            $name,
        Collection        $type,
        ManagedCollection $values,
    ): ActionSet
    {
        $request = dispatch(new GetStoreRequest(
            $store->storage(),
            $this->namingStrategy->determine($store->name(), $name)
        ));

        $joinTable = $request->store;
        $queries = new QuerySet();

        foreach ($values->getAdditions() as $order => $value) {
            foreach ($this->insertBuilder->build(
                $joinTable,
                ['id' => $entity, 'value' => $value, 'order' => $order],
                Priority::UpdateCollection,
            ) as $query) {
                $queries[] = $query;
            }
        }

        foreach ($values->getDeletions() as $value) {
            foreach ($this->deleteBuilder->build(
                $joinTable,
                ['id' => $entity, 'value' => $value],
                Priority::UpdateCollection,
            ) as $query) {
                $queries[] = $query;
            }
        }

        foreach ($values->getModifications() as $order => $value) {
            foreach ($this->updateBuilder->build(
                $joinTable,
                ['order' => $order],
                ['id' => $entity, 'value' => $value],
            ) as $query) {
                $queries[] = $query;
            }
        }

        return $queries;
    }
}
