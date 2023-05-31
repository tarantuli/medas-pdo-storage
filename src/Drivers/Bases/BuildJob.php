<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Queries\QueryCollection;
use Medas\StorageManager\Structure\{Blueprint, Blueprint\Field, Changes\Changes};

class BuildJob
{
    public Changes $changes;
    public string|null $baseQuery = null;
    public string|null $dropForeignKeysQuery = null;
    public string|null $addForeignKeysQuery = null;
    /** @var Field[] */
    public array $collections = [];

    public array $foreignKeys = [];
    public QueryCollection $queryCollection;

    public function __construct(
        public Blueprint $blueprint,
    )
    {
        $this->queryCollection = new QueryCollection();
    }
}
