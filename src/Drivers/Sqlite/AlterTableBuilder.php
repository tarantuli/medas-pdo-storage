<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Sqlite;

use Medas\PdoStorage\Drivers\Bases\BaseAlterTableBuilder;
use Medas\PdoStorage\Drivers\Mysql\ForeignKeyConstraintBuilder;

class AlterTableBuilder extends BaseAlterTableBuilder
{
    private ForeignKeyConstraintBuilder $foreignKeyConstraintBuilder;

    protected function initialize(): void
    {
        // TODO this is wrong, it should be an sqlite specific builder!
        $this->foreignKeyConstraintBuilder = new ForeignKeyConstraintBuilder();
    }

    public function foreignKeyConstraintBuilder(): ForeignKeyConstraintBuilder
    {
        return $this->foreignKeyConstraintBuilder;
    }
}
