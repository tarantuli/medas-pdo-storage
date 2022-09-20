<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\CreateTable;

use Medas\PdoStorage\Database;
use Medas\PdoStorage\Queries\Query;
use Medas\PdoStorage\Structure\Blueprint;

abstract class BaseBuilder
{
    protected Blueprint $blueprint;
    protected string $query;

    public function __construct(
        protected readonly Database $database,
    )
    {
    }

    public function create(Blueprint $blueprint): Query
    {
        $this->blueprint = $blueprint;
        $this->query = sprintf(/** @lang text */ "CREATE TABLE %s (\n", $this->database->quoteIdentifier($this->blueprint->name));

        $this->addFields();
        $this->addKeys();
        $this->addForeignKeys();

        $this->query = substr($this->query, 0, -2);
        $this->query .= "\n)\n";

        return new Query($this->query, [], $this->database);
    }

    protected function addFields(): void
    {
        foreach ($this->blueprint->fields as $field) {
            $this->query .= sprintf(
                " %s %s%s%s%s,\n",
                $this->database->quoteIdentifier($field->name),
                $field->definition,
                $field->isNullable ? '' : ' NOT NULL',
                $field->isGenerated ? $this->isGeneratedDefinition() : '',
                $field->hasDefault ? ' DEFAULT ' . $this->database->escapeValue($field->default) : '',
            );
        }

        // Todo: fix this sqlite hack; definitions should come from Type instances and engine specific handlers
        $this->query = str_replace('int unsigned NOT NULL INTEGER PRIMARY KEY', 'INTEGER PRIMARY KEY', $this->query);
    }

    abstract function isGeneratedDefinition(): string;

    protected function addKeys(): void
    {
        foreach ($this->blueprint->indexes as $index) {
            if ($index->name === 'PRIMARY') {
                $this->query .= ' PRIMARY KEY (';
            }
            else {
                if ($index->isUnique) {
                    $this->query .= ' UNIQUE';
                }
                $this->query .= ' KEY ' . $this->database->quoteIdentifier($index->name) . ' (';
            }

            foreach ($index->fields as $field) {
                $this->query .= $this->database->quoteIdentifier($field->name) . ',';
            }

            $this->query = substr($this->query, 0, -1) . "),\n";
        }
    }

    protected function addForeignKeys(): void
    {
        foreach ($this->blueprint->foreignKeys as $name => $foreignKey) {
            $this->query .= ' CONSTRAINT ' . $this->database->quoteIdentifier($name) . "\n"
                . '   FOREIGN KEY (' . $this->database->quoteIdentifier($foreignKey->field) . ")\n"
                . '   REFERENCES ' . $this->database->quoteIdentifier($foreignKey->foreignEntity)
                . ' (' . $this->database->quoteIdentifier($foreignKey->foreignField) . "),\n";
        }
    }
}
