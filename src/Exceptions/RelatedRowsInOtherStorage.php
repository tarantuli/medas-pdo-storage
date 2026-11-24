<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;

// A subquery can only read a table of the same database as the query around it.
class RelatedRowsInOtherStorage extends BaseException
{
    public function __construct(string $entity)
    {
        parent::__construct($entity);
    }

    public function pattern(): string
    {
        return 'the related rows of %s are kept in another storage than the selected entity';
    }
}
