<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders\Shared;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\Slice;
use Medas\PdoStorage\Exceptions\OffsetIsNotAllowed;

#[Service]
readonly class SliceAppender
{
    public function append(string &$query, Slice|null $slice, bool $allowOffset): void
    {
        if ($slice === null) {
            return;
        }

        if (!$allowOffset && $slice->from > 0) {
            throw new OffsetIsNotAllowed();
        }

        if ($allowOffset) {
            $query .= ' limit ' . $slice->count . ' offset ' . $slice->from;
        }
        else {
            $query .= ' limit ' . $slice->count;
        }
    }
}
