<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\StorageManager\Interfaces\Store;

readonly class Table implements Store
{
    public function __construct(
        public Database $database,
        public string   $name,
    )
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function storage(): Database
    {
        return $this->database;
    }
}
