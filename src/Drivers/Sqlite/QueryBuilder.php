<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Sqlite;

use Medas\PdoStorage\Drivers\Bases\BaseQueryBuilder;
use Medas\PdoStorage\Queries\Query;
use Medas\PdoStorage\Table;

class QueryBuilder extends BaseQueryBuilder
{
    public function showTables(?string $name): Query
    {
        return new Query(sprintf(
            'select name from sqlite_schema where type="table" and name not like "sqlite_%%"%s',
            $name === null ? '' : 'and name like "' . $name . '"'
        ));
    }

    public function showCreate(Table $table): Query
    {
        return new Query('select sql from sqlite_schema where name like ' . $this->driver->quote($table->name));
    }
}
