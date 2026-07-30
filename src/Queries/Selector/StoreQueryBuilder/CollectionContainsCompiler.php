<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector\StoreQueryBuilder;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\Operants\Property;
use Medas\PdoStorage\ConfigOptions\JoinTables\TableNamingStrategy;
use Medas\PdoStorage\JoinTables\NamingStrategy;

/**
 * Compiles a collection-membership test into an `exists` subquery against the
 * property's join table. A collection property has no column on the owner
 * store; its members live in a join table (`<store>__<property>`, columns
 * `id` = owner id, `value` = element id), so membership is expressed as an
 * `exists` against that table rather than an `in` on a column.
 */
#[Service]
readonly class CollectionContainsCompiler
{
    public function __construct(
        private MetaDataManager $metaDataManager,

        #[ConfigValue(TableNamingStrategy::class)]
        private NamingStrategy  $namingStrategy,
    )
    {
    }

    /**
     * @param string $valueQuery the already-compiled expression for the
     *                           element id to test for (e.g. a `:group`
     *                           placeholder produced by the caller)
     */
    public function compile(Job $job, Property $property, string $valueQuery): string
    {
        $ownerEntity = $property->entity ?? $job->mainEntity;
        $ownerStore = $this->metaDataManager->get($ownerEntity)->entity->store;
        $joinTable = $this->namingStrategy->determine($ownerStore, $property->name);
        $quotedJoinTable = $job->driverHandler->quote($job->database, $joinTable);
        $joinOwnerId = $quotedJoinTable . '.' . $job->driverHandler->quote($job->database, 'id');
        $joinValue = $quotedJoinTable . '.' . $job->driverHandler->quote($job->database, 'value');

        $ownerId = $job->stores[$ownerEntity]
            . '.'
            . $job->driverHandler->quote($job->database, 'id');

        return 'exists (select 1 from '
            . $quotedJoinTable
            . ' where '
            . $joinOwnerId
            . ' = '
            . $ownerId
            . ' and '
            . $joinValue
            . ' = '
            . $valueQuery
            . ')';
    }
}
