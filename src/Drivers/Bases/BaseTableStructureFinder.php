<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Drivers\Bases\TableStructureFinder\DefinitionHandler;
use Medas\PdoStorage\Drivers\Interfaces\TableStructureFinder;
use Medas\PdoStorage\Table;
use Medas\StorageManager\Structure\Blueprint;

abstract class BaseTableStructureFinder implements TableStructureFinder
{
    protected Blueprint $blueprint;
    protected string|null $createTable;

    private DefinitionHandler $definitionHandler;

    public function __construct()
    {
        $this->definitionHandler = new DefinitionHandler();
    }

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

    abstract protected function findName();

    protected function findFields(): void
    {
        if (!preg_match_all('/^ +`([^`]+)` (.+?),?$/m', $this->createTable, $matches, PREG_SET_ORDER)) {
            return;
        }

        foreach ($matches as $match) {
            $this->blueprint->addField($this->definitionHandler->convertToField($match[1], $match[2]));
        }
    }

    abstract protected function findPrimaryKey();

    abstract protected function findKeys();
}
