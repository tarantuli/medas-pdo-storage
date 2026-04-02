<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\PdoStorage\Queries\Query;

class PdoDatabase extends BaseException
{
    public function __construct(
        string                        $message,
        Query                         $query,
        private readonly \Throwable   $previous,
        public readonly ExceptionType $exceptionType,
    )
    {
        parent::__construct($message, $query->query, $query->arguments, $this->exceptionType);
    }

    public function pattern(): string
    {
        return 'error %s when executing %s with arguments %s';
    }

    public function previous(): \Throwable|null
    {
        return $this->previous;
    }
}
