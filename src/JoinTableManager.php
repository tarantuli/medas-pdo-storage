<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\Service;

#[Service]
class JoinTableManager
{
    public function determineName(string $sourceTable, string $property): string
    {
        return $sourceTable . '__' . $property;
    }
}
