<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders\Shared;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\Slice;

#[Service]
readonly class SliceAppender
{
    public function append(string &$query, Slice|null $slice): void
    {
        if ($slice === null) {
            return;
        }

        $query .= ' limit ' . $slice->from . ', ' . $slice->count;
    }
}
