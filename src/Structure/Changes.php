<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure;

class Changes
{
    /** @var Blueprint\Field[] */
    public array $addFields = [];

    /** @var Blueprint\Field[] */
    public array $changeFields = [];

    /** @var Blueprint\Index[] */
    public array $indexes = [];

    public function __construct(public string $name)
    {
    }
}
