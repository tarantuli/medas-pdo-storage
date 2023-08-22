<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\StorageManager\Interfaces\ActionBuilders;
use Medas\StorageManager\Interfaces\Builders;

interface QueryBuilders extends ActionBuilders
{
    public function createStore(): Builders\CreateStoreBuilder;

    public function selectorQuery(): Builders\SelectorActionBuilder;

    public function insert(): Builders\InsertBuilder;

    public function get(): Builders\GetBuilder;

    public function update(): Builders\UpdateBuilder;

    public function delete(): Builders\DeleteBuilder;

    public function collectionUpdate(): Builders\CollectionUpdateBuilder;

//    public function insert(Table $table, array $values): QueryCollection;
//
//    /** @param Table[] $tables */
//    public function select(array $tables, array $filters): QueryCollection;
//
//    public function update(Table $table, array $updates, array $conditions): QueryCollection;
//
//    public function delete(Table $table, array $conditions): QueryCollection;
//
//    public function collectionUpdate(Table $table, object $entity, string $name, Collection $type, ManagedCollection $values): QueryCollection;
//
//    public function showCreate(Table $table): QueryCollection;
//
//    public function extractCreateStatement(Record $record): string;
//
//    public function dropTable(string $name): QueryCollection;
//
    public function showTables(): ShowTablesBuilder;
}
