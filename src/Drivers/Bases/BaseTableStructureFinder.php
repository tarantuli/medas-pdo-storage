<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\PdoStorage\Drivers\Interfaces\TableStructureFinder;
use Medas\PdoStorage\Table;
use Medas\StorageManager\Structure\Blueprint;

abstract class BaseTableStructureFinder implements TableStructureFinder
{
    protected Blueprint $blueprint;
    protected string|null $createTable;

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
        $this->findForeignKeys();

        return $this->blueprint;
    }

    abstract protected function findName();

    abstract protected function findFields();

    abstract protected function findPrimaryKey();

    abstract protected function findKeys();

    abstract protected function findForeignKeys();
}
