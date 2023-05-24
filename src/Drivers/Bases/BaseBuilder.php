<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Database;
use Medas\PdoStorage\Drivers\Handler;
use Medas\PdoStorage\JoinTableManager;
use Medas\PdoStorage\Queries\QueryCollection;
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\Structure\Blueprint\Field;

abstract class BaseBuilder
{
    protected Blueprint $blueprint;
    /** @var Field[] */
    protected array $collections;
    protected QueryCollection $queryCollection;

    public function __construct(
        protected readonly Handler  $driver,
        protected readonly Database $database,
    )
    {
        $this->initialize();
    }

    abstract protected function initialize(): void;

    protected function processCollections(): void
    {
        if (!$this->collections) {
            return;
        }

        $joinTableManager = service(JoinTableManager::class);

        foreach ($this->collections as $collectionField) {
            $queries = $joinTableManager->createQueries($this->database, $this->driver, $this->blueprint, $collectionField);

            foreach ($queries as $query) {
                $this->queryCollection[] = $query;
            }
        }
    }
}
