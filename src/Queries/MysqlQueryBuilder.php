<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\PdoStorage\Queries\CreateTable\MysqlCreateTableBuilder;
use Medas\PdoStorage\Structure\Blueprint;

class MysqlQueryBuilder extends BaseSqlQueryBuilder
{
    private MysqlCreateTableBuilder $createTableBuilder;

    protected function initialize()
    {
        $this->createTableBuilder = new MysqlCreateTableBuilder($this->database);
    }

    public function showTables(?string $name): Query
    {
        return new Query('SHOW TABLES LIKE "' . $name . '"');
    }

    public function createTable(Blueprint $blueprint): Query
    {
        return $this->createTableBuilder->create($blueprint);
    }
}
