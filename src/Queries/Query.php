<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\PdoStorage\{Database, Exceptions\StatementIsNotSet, Statement};
use Medas\StorageManager\{Interfaces\RecordSet, UnitOfWork\BaseAction, UnitOfWork\Priority};

class Query extends BaseAction
{
    public array $serializedArguments = [];
    public Statement|null $statement = null;

    public function __construct(
        public readonly string   $query,
        public array             $arguments,
        public readonly Database $database,
        Priority                 $priority = Priority::Default
    )
    {
        parent::__construct($this->database, $priority);
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

    public function recordSet(): RecordSet
    {
        return $this->statement ?? throw new StatementIsNotSet();
    }
}
