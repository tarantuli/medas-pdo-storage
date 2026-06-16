<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\MetaData\Property as MetaDataProperty;
use Medas\EntityManager\Selector\{
    Conditions\WhereIs,
    Operants\Argument,
    Operants\Property,
    Parameter,
    Sorting\SortBy
};
use Medas\PdoStorage\ConfigOptions\JoinTables\TableNamingStrategy;
use Medas\PdoStorage\Events\{ExecuteSetRequest, GetStoreRequest};
use Medas\PdoStorage\JoinTables\NamingStrategy;
use Medas\PdoStorage\Table;
use Medas\StorageManager\Interfaces\{
    Fetchers\CollectionRecordFetcher as CollectionRecordFetcherInterface,
    Store
};

#[Service]
readonly class CollectionRecordFetcher implements CollectionRecordFetcherInterface
{
    public function __construct(
        private DefinitionQueryBuilder $definitionQueryBuilder,

        #[ConfigValue(TableNamingStrategy::class)]
        private NamingStrategy         $namingStrategy,
    )
    {
    }

    public function fetch(Store $store, object $entity, MetaDataProperty $property): iterable
    {
        $joinTable = $this->getJoinTable($store, $property);

        $actionSet = $this->definitionQueryBuilder->build(
            $joinTable,
            [
                WhereIs::c(Property::c('id'), Argument::c('entity')),
                SortBy::c(Property::c('order')),
                Parameter::c('entity'),
            ],
            ['entity' => $entity],
        );

        dispatch(new ExecuteSetRequest($actionSet));

        return $actionSet->lastRecordSet->fetchRecords();
    }

    private function getJoinTable(Store $store, MetaDataProperty $property): Table
    {
        $joinTableName = $this->namingStrategy->determine($store->name(), $property->name);
        $request = dispatch(new GetStoreRequest($store->storage(), $joinTableName));

        return $request->store;
    }
}
