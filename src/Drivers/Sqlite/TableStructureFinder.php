<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Sqlite;

use Medas\PdoStorage\Drivers\Bases\BaseTableStructureFinder;
use Medas\StorageManager\Structure\Blueprint\Field;
use Medas\StorageManager\Structure\Blueprint\Index;

class TableStructureFinder extends BaseTableStructureFinder
{
    protected function findName(): void
    {
        if (!preg_match('/CREATE TABLE "([^"]+)/', $this->createTable, $match)) {
            return;
        }

        $this->blueprint->name = $match[1];
    }

    protected function findFields(): void
    {
        if (!preg_match_all('/^ +"([^"]+)" (.+?),?$/m', $this->createTable, $matches, PREG_SET_ORDER)) {
            return;
        }
        foreach ($matches as $match) {
            $definition = $match[2];
            $isNullable = true;
            $isGenerated = false;

            // Strip collation
            $definition = preg_replace('/ COLLATE \w+/', '', $definition);

            if (str_ends_with($definition, ' AUTO_INCREMENT')) {
                $isNullable = false;
                $isGenerated = true;
                $definition = substr($definition, 0, -strlen(' AUTO_INCREMENT'));
            }

            if (str_ends_with($definition, ' NOT NULL')) {
                $isNullable = false;
                $definition = substr($definition, 0, -strlen(' NOT NULL'));
            }

            # Todo: parse defaults

            $this->blueprint->fields[] = new Field($match[1], $definition, $isNullable, $isGenerated);
        }
    }

    protected function findPrimaryKey(): void
    {
        if (!preg_match('/PRIMARY KEY \(([^)]+)\)/', $this->createTable, $match)) {
            return;
        }

        $index = new Index(isPrimary: true);
        $index->fields = $this->blueprint->fields[] = $this->getNames($match[1]);
        $index->isUnique = true;

        $this->blueprint->indexes[] = $index;
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
            $index = new Index($match['name']);
            $index->fields = $this->blueprint->fieldsByName($this->getNames($match['fields']));
            $index->isUnique = isset($match['isUnique']);

            $this->blueprint->indexes[] = $index;
        }
    }
}
