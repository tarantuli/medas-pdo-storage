<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\PdoStorage\Structure\Blueprint;
use Medas\PdoStorage\Table;

interface QueryBuilder
{
    public function create(Table $table, array $values): Query;

    /** @param Table[] $tables */
    public function select(array $tables, array $filters): Query;

    public function update(Table $table, array $updates, array $conditions): Query;

    public function delete(Table $table, array $conditions): Query;

    public function showCreate(Table $table): Query;

    public function dropTable(string $name): Query;

    public function showTables(string|null $name): Query;

    public function createTable(Blueprint $blueprint): Query;
}
