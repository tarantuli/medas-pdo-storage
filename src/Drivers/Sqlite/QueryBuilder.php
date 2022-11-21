<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Sqlite;

use Medas\PdoStorage\Drivers\Bases\BaseQueryBuilder;
use Medas\PdoStorage\Queries\Query;

class QueryBuilder extends BaseQueryBuilder
{
    public function showTables(?string $name): Query
    {
        return new Query(sprintf(
            'SELECT name FROM sqlite_schema WHERE type="table" and name NOT LIKE "sqlite_%%"%s',
            $name === null ? '' : 'and name LIKE "' . $name . '"'
        ));
    }
}
