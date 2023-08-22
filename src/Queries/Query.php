<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\PdoStorage\{Database, Statement};
use Medas\StorageManager\{Interfaces\RecordSet, UnitOfWork\BaseAction, UnitOfWork\Priority};

class Query extends BaseAction
{
    public array $serializedArguments = [];
    private Statement $statement;

    public function __construct(
        public readonly string        $query,
        public array                  $arguments = [],
        public readonly Database|null $database = null,
        Priority                      $priority = Priority::Default
    )
    {
        $this->priority = $priority;
    }

    public function setStatement(Statement $statement): self
    {
        $this->statement = $statement;

        return $this;
    }

    public function recordSet(): RecordSet
    {
        return $this->statement;
    }
}
