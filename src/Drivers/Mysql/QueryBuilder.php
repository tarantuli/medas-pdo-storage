<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Mysql;

use Medas\PdoStorage\Drivers\Bases\BaseQueryBuilder;
use Medas\PdoStorage\Queries\Query;

class QueryBuilder extends BaseQueryBuilder
{
    public function showTables(?string $name): Query
    {
        return new Query('SHOW TABLES LIKE "' . $name . '"');
    }
}
