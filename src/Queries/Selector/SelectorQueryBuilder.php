<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector;

use Medas\Core\{Attributes\Service, Interfaces\NotCacheable};
use Medas\EntityManager\{MetaDataManager, Selector\Selector};
use Medas\PdoStorage\{Database, Exceptions\StorageIsNotDatabase, Queries\QuerySet};
use Medas\StorageManager\Interfaces\Builders\SelectorActionBuilder;
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\UnitOfWork\ActionSet;

#[Service]
readonly class SelectorQueryBuilder implements SelectorActionBuilder
{
    public function __construct(
        private MetaDataManager           $metaDataManager,
        private ParameterizedQueryToQuery $parameterizedQueryToQuery,
        private StorageManager            $storageManager,
        private StoreQueryBuilder         $storeQueryBuilder,
    )
    {
    }

    public function build(Selector $selector, array $arguments, bool $doCount = false): ActionSet
    {
        if ($selector instanceof NotCacheable) {
            $paraQuery = $this->buildParameterizedQuery($selector, $doCount);
        }
        else {
            /** @var ParameterizedQuery $paraQuery */
            $paraQuery = cache(
                [static::class, $selector::class . $doCount],
                fn() => $this->buildParameterizedQuery($selector, $doCount),
            );
        }

        return new QuerySet([$this->parameterizedQueryToQuery->compile($paraQuery, $arguments)]);
    }

    private function buildParameterizedQuery(Selector $selector, bool $doCount): ParameterizedQuery
    {
        $definition = $selector->definition();
        $metaData = $this->metaDataManager->get($selector->entity());
        $database = $this->storageManager->byName($metaData->entity->storage);

        if (!$database instanceof Database) {
            throw new StorageIsNotDatabase($metaData->entity->storage);
        }

        return $this->storeQueryBuilder->buildQuery(
            $database,
            $definition,
            $metaData->entity->store,
            $metaData->className,
            $doCount
        );
    }
}
