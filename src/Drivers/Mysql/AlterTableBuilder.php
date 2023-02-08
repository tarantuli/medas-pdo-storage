<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Mysql;

use Medas\PdoStorage\Drivers\Bases\BaseAlterTableBuilder;

class AlterTableBuilder extends BaseAlterTableBuilder
{
    private ForeignKeyConstraintBuilder $foreignKeyConstraintBuilder;

    protected function initialize(): void
    {
        $this->foreignKeyConstraintBuilder = new ForeignKeyConstraintBuilder();
    }

    function foreignKeyConstraintBuilder(): ForeignKeyConstraintBuilder
    {
        return $this->foreignKeyConstraintBuilder;
    }
}
