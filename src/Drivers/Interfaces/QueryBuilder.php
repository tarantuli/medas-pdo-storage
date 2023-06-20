<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\Core\Interfaces\ManagedCollection;
use Medas\EntityManager\Types\Collection;
use Medas\PdoStorage\Queries\{QueryCollection};
use Medas\PdoStorage\Table;
use Medas\StorageManager\Interfaces\ActionBuilder;

interface QueryBuilder extends ActionBuilder
{
    public function create(Table $table, array $values): QueryCollection;

    /** @param Table[] $tables */
    public function select(array $tables, array $filters): QueryCollection;

    public function update(Table $table, array $updates, array $conditions): QueryCollection;

    public function delete(Table $table, array $conditions): QueryCollection;

    public function collectionUpdate(Table $table, object $entity, string $name, Collection $type, ManagedCollection $values): QueryCollection;

    public function showCreate(Table $table): QueryCollection;

    public function dropTable(string $name): QueryCollection;

    public function showTables(string|null $name): QueryCollection;
}
