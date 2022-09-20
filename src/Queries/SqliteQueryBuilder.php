<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\PdoStorage\Queries\CreateTable\SqliteCreateTableBuilder;
use Medas\PdoStorage\Structure\Blueprint;

class SqliteQueryBuilder extends BaseSqlQueryBuilder
{
    private SqliteCreateTableBuilder $createTableBuilder;

    protected function initialize()
    {
        $this->createTableBuilder = new SqliteCreateTableBuilder($this->database);
    }

    public function showTables(string|null $name): Query
    {
        return new Query(sprintf(
            'SELECT name FROM sqlite_schema WHERE type="table" and name NOT LIKE "sqlite_%%"%s',
            $name === null ? '' : 'and name LIKE "' . $name . '"'
        ));
    }

    public function createTable(Blueprint $blueprint): Query
    {
        return $this->createTableBuilder->create($blueprint);
    }
}
