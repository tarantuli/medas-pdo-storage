<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Mysql;

use Medas\PdoStorage\Drivers\Bases\BaseTableStructureFinder;
use Medas\StorageManager\Structure\Blueprint\ForeignKey;
use Medas\StorageManager\Structure\Blueprint\Index;

class TableStructureFinder extends BaseTableStructureFinder
{
    protected function findName(): void
    {
        if (!preg_match('/create table `([^`]+)/i', $this->createTable, $match)) {
            return;
        }

        $this->blueprint->setName($match[1]);
    }

    protected function findPrimaryKey(): void
    {
        if (!preg_match('/primary key \(([^)]+)\)/i', $this->createTable, $match)) {
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

        return array_map(fn($name) => trim($name, '`'), $names);
    }

    protected function findKeys(): void
    {
        if (!preg_match_all(
            '/(?<isUnique>unique )?key `(?<name>[^`]+)` \((?<fields>[^)]+)\)/i',
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

    protected function findForeignKeys(): void
    {
        if (!preg_match_all(
            '/constraint `(?<name>[^`]+)` foreign key \(`(?<field>[^`]+)`\) references `(?<table>[^`]+)` \(`(?<reference>[^`]+)`\)(?<onDeleteCascade> on delete cascade)?/i',
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
