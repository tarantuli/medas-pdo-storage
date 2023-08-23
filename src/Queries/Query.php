<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\PdoStorage\{Database, Statement};
use Medas\StorageManager\{Interfaces\RecordSet, UnitOfWork\BaseAction, UnitOfWork\Priority};

class Query extends BaseAction
{
    public array $serializedArguments = [];
    public Statement $statement;

    public function __construct(
        public readonly string   $query,
        public array             $arguments,
        public readonly Database $database,
        Priority                 $priority = Priority::Default
    )
    {
        $this->storage = $this->database;
        $this->priority = $priority;
    }

    public function recordSet(): RecordSet
    {
        return $this->statement;
    }

    public function __serialize(): array
    {
        return [
            'query' => $this->query,
            'arguments' => $this->arguments,
            'database' => $this->database,
            'priority' => $this->priority,
        ];
    }
}
