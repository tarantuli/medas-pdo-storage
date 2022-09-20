<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\PdoStorage\Database;
use Medas\PdoStorage\Statement;
use Medas\StorageManager\Interfaces\RecordSet;
use Medas\StorageManager\UnitOfWork\{ActionTypes\Generic, BaseAction};

class Query extends BaseAction
{
    public array $serializedArguments = [];
    private Statement $statement;

    public function __construct(
        public readonly string $query,
        public array           $arguments = [],
        Database               $database = null
    )
    {
        $this->storage = $database ?: storage();
        $this->type = Generic::instance();
    }

    public function execute(): void
    {
        $this->storage->execute($this);
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
