<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector\StoreQueryBuilder;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\{
    Exceptions\UnhandledRelationType,
    Relations\Join,
    Relations\Relation
};
use Medas\StorageManager\StorageManager;

#[Service]
readonly class RelationsProcessor
{
    public function __construct(
        private MetaDataManager $metaDataManager,
        private StorageManager  $storageManager,
    )
    {
    }

    /** @param Relation[] $relations */
    public function process(Job $job, array $relations): void
    {
        foreach ($relations as $relation) {
            if ($relation instanceof Join) {
                $targetEntity = $this->metaDataManager->get($relation->targetEntity)->entity;

                $targetStore = $job->driverHandler->quote(
                    $this->storageManager->byName($targetEntity->storage),
                    $targetEntity->store,
                );

                $job->stores[$relation->targetEntity] = $targetStore;

                $job->query .= ' join '
                    . $targetStore
                    . ' on ('
                    . $job->stores[$job->mainEntity]
                    . '.'
                    . $job->driverHandler->quote($job->database, $relation->sourceProperty)
                    . ' = '
                    . $targetStore
                    . '.'
                    . $job->driverHandler->quote($job->database, $relation->targetProperty)
                    . ')';
            }
            else {
                throw new UnhandledRelationType($relation);
            }
        }
    }
}
