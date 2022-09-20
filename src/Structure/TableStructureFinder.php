<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure;

use Medas\PdoStorage\Table;
use Medas\ServiceManager\Attributes\Service;

#[Service]
class TableStructureFinder
{
    private Blueprint $blueprint;
    private string|null $createTable;

    public function find(Table $table): Blueprint|null
    {
        $this->blueprint = new Blueprint();
        $this->createTable = $table->getCreateTable();

        if ($this->createTable === null) {
            return null;
        }

        $this->findName();
        $this->findFields();
        $this->findPrimaryKey();
        $this->findKeys();

        return $this->blueprint;
    }

    private function findName(): void
    {
        if (!preg_match('/CREATE TABLE `([^`]+)/', $this->createTable, $match)) {
            return;
        }

        $this->blueprint->name = $match[1];
    }

    private function findFields(): void
    {
        if (!preg_match_all('/^ +`([^`]+)` (.+?),?$/m', $this->createTable, $matches, PREG_SET_ORDER)) {
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

            $this->blueprint->addField(new Blueprint\Field($match[1], $definition, $isNullable, $isGenerated));
        }
    }

    private function findPrimaryKey(): void
    {
        if (!preg_match('/PRIMARY KEY \(([^)]+)\)/', $this->createTable, $match)) {
            return;
        }

        $index = new Blueprint\Index('PRIMARY');
        $index->fields = $this->blueprint->fields($this->getNames($match[1]));
        $index->isUnique = true;

        $this->blueprint->addIndex($index);
    }

    private function getNames(string $nameString): array
    {
        $names = explode(',', $nameString);

        return array_map(fn($name) => trim($name, '`'), $names);
    }

    private function findKeys(): void
    {
        if (!preg_match_all(
            '/(?<isUnique>UNIQUE )?KEY `(?<name>[^`]+)` \((?<fields>[^)]+)\)/',
            $this->createTable,
            $matches,
            PREG_SET_ORDER
        )) {
            return;
        }

        foreach ($matches as $match) {
            $index = new Blueprint\Index($match['name']);
            $index->fields = $this->blueprint->fields($this->getNames($match['fields']));
            $index->isUnique = isset($match['isUnique']);

            $this->blueprint->addIndex($index);
        }
    }
}
