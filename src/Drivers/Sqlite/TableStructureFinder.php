<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Sqlite;

use Medas\PdoStorage\Drivers\Bases\BaseTableStructureFinder;
use Medas\StorageManager\Structure\Blueprint\ForeignKey;
use Medas\StorageManager\Structure\Blueprint\Index;

class TableStructureFinder extends BaseTableStructureFinder
{
    private DefinitionHandler $definitionHandler;

    public function __construct()
    {
        $this->definitionHandler = new DefinitionHandler();
    }

    protected function findName(): void
    {
        if (!preg_match('/create table "([^"]+)/', $this->createTable, $match)) {
            return;
        }

        $this->blueprint->setName($match[1]);
    }

    protected function findFields(): void
    {
        if (!preg_match_all('/^ +"([^"]+)" (.+?),?$/m', $this->createTable, $matches, PREG_SET_ORDER)) {
            return;
        }

        foreach ($matches as $match) {
            $this->blueprint->addField($this->definitionHandler->convertToField($match[1], $match[2]));
        }
    }

    protected function findPrimaryKey(): void
    {
        if (!preg_match('/primary key \(([^)]+)\)/', $this->createTable, $match)) {
            return;
        }

        $index = new Index(isPrimary: true);

        foreach ($this->getNames($match[1]) as $name) {
            $index->addField($this->blueprint->fieldByName($name));
        }

        $index->isUnique = true;

        $this->blueprint->addIndex($index);
    }

    protected function getNames(string $nameString): array
    {
        $names = explode(',', $nameString);

        return array_map(fn($name) => trim($name, '"'), $names);
    }

    protected function findKeys(): void
    {
        if (!preg_match_all(
            '/(?<isUnique>unique )? "(?<name>[^"]+)" \((?<fields>[^)]+)\)/',
            $this->createTable,
            $matches,
            PREG_SET_ORDER
        )) {
            return;
        }

        foreach ($matches as $match) {
            $index = new Index();

            foreach ($this->blueprint->fieldsByName($this->getNames($match['fields'])) as $field) {
                $index->addField($field);
            }

            $index->isUnique = isset($match['isUnique']);

            $this->blueprint->addIndex($index);
        }
    }

    protected function findForeignKeys()
    {
        if (!preg_match_all(
            '/constraint "(?<name>[^"]+)"\s+foreign key \("(?<field>[^"]+)"\)\s+references "(?<table>[^"]+)" \("(?<reference>[^"]+)"\)(?<onDeleteCascade> on delete cascade)?/i',
            $this->createTable,
            $matches,
            PREG_SET_ORDER
        )) {
            return;
        }

        foreach ($matches as $match) {
            $foreignKey = new ForeignKey($match['field'], $match['table'], $match['reference'], isset($match['onDeleteCascade']));
            $this->blueprint->addForeignKey($foreignKey);
        }
    }
}
