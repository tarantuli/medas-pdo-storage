<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\{BaseException, Suggestions};

class DriverNotImplemented extends BaseException implements Suggestions
{
    public function __construct(
        private readonly string $driverName,
    )
    {
        parent::__construct($driverName);
    }

    public function pattern(): string
    {
        return 'pdo driver %s is not implemented';
    }

    public function suggestions(): array
    {
        return match ($this->driverName) {
            'mysql' => ['try morphp/medas-pdo-mysql'],
            'sqlite' => ['try morphp/medas-pdo-sqlite'],
            default => [],
        };
    }
}
