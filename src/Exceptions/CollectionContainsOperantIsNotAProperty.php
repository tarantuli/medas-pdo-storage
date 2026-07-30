<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\EntityManager\Selector\Operants\Operant;

class CollectionContainsOperantIsNotAProperty extends BaseException
{
    public function __construct(Operant $operant)
    {
        parent::__construct($operant::class);
    }

    public function pattern(): string
    {
        return 'WhereCollectionContains operant must be a Property, %s found';
    }
}
