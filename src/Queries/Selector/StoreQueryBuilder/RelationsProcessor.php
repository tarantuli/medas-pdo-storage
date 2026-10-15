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
use Medas\PdoStorage\Database;
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

    /**
     * Makes the joined stores known before the joins are written, for the output values: they come first in the query
     *
     * @param Relation[] $relations
     */
    public function registerStores(Job $job, array $relations): void
    {
        foreach ($relations as $relation) {
            if ($relation instanceof Join) {
                $job->stores[$relation->targetEntity] = $this->targetStore($job, $relation);
            }
        }
    }

    /** @param Relation[] $relations */
    public function process(Job $job, array $relations): void
    {
        foreach ($relations as $relation) {
            if ($relation instanceof Join) {
                $targetStore = $this->targetStore($job, $relation);
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

    private function targetStore(Job $job, Join $relation): string
    {
        $targetEntity = $this->metaDataManager->get($relation->targetEntity)->entity;

        /** @var Database $database */
        $database = $this->storageManager->byName($targetEntity->storage);

        return $job->driverHandler->quote($database, $targetEntity->store);
    }
}
