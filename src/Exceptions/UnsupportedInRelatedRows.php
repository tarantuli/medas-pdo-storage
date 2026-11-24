<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;

// The definition of related rows becomes a correlated subquery, which only takes its
// conditions and sorts: anything else in it would be left out without a word.
class UnsupportedInRelatedRows extends BaseException
{
    public function __construct(string $entity, string $what)
    {
        parent::__construct($entity, $what);
    }

    public function pattern(): string
    {
        return 'the related rows of %s cannot have %s';
    }
}
