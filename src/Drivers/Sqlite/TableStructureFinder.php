<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Sqlite;

use Medas\PdoStorage\Drivers\Bases\BaseTableStructureFinder;
use Medas\StorageManager\Structure\Blueprint\Index;

class TableStructureFinder extends BaseTableStructureFinder
{
    protected function findName(): void
    {
        if (!preg_match('/CREATE TABLE "([^"]+)/', $this->createTable, $match)) {
            return;
        }

        $this->blueprint->setName($match[1]);
    }

    protected function findPrimaryKey(): void
    {
        if (!preg_match('/PRIMARY KEY \(([^)]+)\)/', $this->createTable, $match)) {
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
            '/(?<isUnique>UNIQUE )?KEY "(?<name>[^"]+)" \((?<fields>[^)]+)\)/',
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
}
