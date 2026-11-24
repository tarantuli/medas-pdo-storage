<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector\StoreQueryBuilder\OutputValueProcessor;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\OutputValues\SwitchCase;
use Medas\PdoStorage\Queries\Selector\StoreQueryBuilder\{CalculationsProcessor, Job};

// The case expression is written by CalculationsProcessor, which also writes it where it
// is part of a condition or a derived value
#[Service]
readonly class SwitchCaseProcessor
{
    public function __construct(
        private CalculationsProcessor $calculationsProcessor,
    )
    {
    }

    public function process(Job $job, SwitchCase $switchCase): string
    {
        return $this->calculationsProcessor->expression($job, $switchCase);
    }
}
