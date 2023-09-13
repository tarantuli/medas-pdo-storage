<?php

declare(strict_types=1);

namespace Medas\PdoStorage\ConfigOptions\JoinTables;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\ConfigGroup;
use Medas\PdoStorage\ConfigOptions\PdoGroup;

#[Service]
readonly class JoinTableGroup implements ConfigGroup
{
    public function __construct(
        private PdoGroup $group,
    )
    {
    }

    public function parent(): ConfigGroup|null
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'join-tables';
    }
}
