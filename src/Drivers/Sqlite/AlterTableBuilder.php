<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Sqlite;

use Medas\PdoStorage\Drivers\Bases\BaseAlterTableBuilder;

class AlterTableBuilder extends BaseAlterTableBuilder
{
    private ForeignKeyConstraintBuilder $foreignKeyConstraintBuilder;

    protected function initialize(): void
    {
        $this->foreignKeyConstraintBuilder = new ForeignKeyConstraintBuilder();
    }

    public function foreignKeyConstraintBuilder(): ForeignKeyConstraintBuilder
    {
        return $this->foreignKeyConstraintBuilder;
    }
}
