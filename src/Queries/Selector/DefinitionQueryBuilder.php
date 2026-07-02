<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\Definition;
use Medas\PdoStorage\{Queries\QuerySet, Table};
use Medas\StorageManager\Interfaces\Store;

#[Service]
readonly class DefinitionQueryBuilder
{
    public function __construct(
        private ParameterizedQueryToQuery $parameterizedQueryToQuery,
        private StoreQueryBuilder         $storeQueryBuilder,
    )
    {
    }

    public function build(Store $store, array $elements, array $arguments): QuerySet
    {
        /** @var Table $store->storage() */
        $definition = new Definition('')
            ->add(...$elements);

        $paraQuery = $this->storeQueryBuilder->buildQuery(
            $store->storage(),
            $definition,
            $store->name(),
        );

        return new QuerySet([$this->parameterizedQueryToQuery->compile($paraQuery, $arguments)]);
    }
}
