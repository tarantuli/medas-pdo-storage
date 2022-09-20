<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\CreateTable;

class MysqlCreateTableBuilder extends BaseBuilder
{
    function isGeneratedDefinition(): string
    {
        return ' AUTO_INCREMENT';
    }
}
