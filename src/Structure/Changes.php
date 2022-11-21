<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure;

use Medas\StorageManager\Structure\Blueprint\Field;
use Medas\StorageManager\Structure\Blueprint\Index;

class Changes
{
    /** @var Field[] */
    public array $addFields = [];

    /** @var Field[] */
    public array $changeFields = [];

    /** @var Index[] */
    public array $indexes = [];

    public function __construct(public string $name)
    {
    }
}
