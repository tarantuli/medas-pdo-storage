<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\CreateTable;

class SqliteCreateTableBuilder extends BaseBuilder
{
    protected function addKeys(): void
    {
        foreach ($this->blueprint->indexes as $index) {
            if ($index->name === 'PRIMARY' || !$index->isUnique) {
                continue;
            }

            $this->query .= ' UNIQUE (';

            foreach ($index->fields as $field) {
                $this->query .= $this->database->quoteIdentifier($field->name) . ',';
            }

            $this->query = substr($this->query, 0, -1) . "),\n";
        }
    }

    function isGeneratedDefinition(): string
    {
        return ' INTEGER PRIMARY KEY';
    }
}
