<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\EntityManager\Selector\Parameter;
use Medas\PdoStorage\Database;
use Medas\PdoStorage\Exceptions\StorageIsNotDatabase;
use Medas\StorageManager\StorageManager;

class ParameterizedQuery
{
    public function __construct(
        public string   $query,
        /** @var Parameter[] $parameters */
        public array    $parameters,
        public array    $constants,
        public Database $database,
    )
    {
    }

    public function __serialize(): array
    {
        return [
            $this->query,
            $this->parameters,
            $this->constants,
            service(StorageManager::class)->getName($this->database),
        ];
    }

    public function __unserialize(array $data): void
    {
        [$this->query, $this->parameters, $this->constants, $name] = $data;

        $database = storage($name);

        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if (!$database instanceof Database) {
            throw new StorageIsNotDatabase($name);
        }

        $this->database = $database;
    }
}
