<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;

class InvalidPaginationPage extends BaseException
{
    public function __construct(int $page)
    {
        parent::__construct($page);
    }

    public function pattern(): string
    {
        return 'Invalid pagination page: %s';
    }
}
