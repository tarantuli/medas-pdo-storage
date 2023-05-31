<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Database;
use Medas\PdoStorage\Drivers\Handler;
use Medas\PdoStorage\JoinTableManager;

abstract class BaseBuilder
{
    public function __construct(
        protected readonly Handler  $driver,
        protected readonly Database $database,
    )
    {
        $this->initialize();
    }

    abstract protected function initialize(): void;

    protected function processCollections(BuildJob $job): void
    {
        if (!$job->collections) {
            return;
        }

        $joinTableManager = service(JoinTableManager::class);

        foreach ($job->collections as $collectionField) {
            $queries = $joinTableManager->createQueries($this->database, $job->blueprint, $collectionField);

            if ($queries) {
                foreach ($queries as $query) {
                    $job->queryCollection[] = $query;
                }
            }
        }
    }
}
