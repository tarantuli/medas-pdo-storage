<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector\StoreQueryBuilder;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\{Exceptions\UndeclaredParameters, Parameter};

#[Service]
readonly class ParametersProcessor
{
    /** @param Parameter[] $parameters */
    public function process(Job $job, array $parameters): void
    {
        foreach ($parameters as $parameter) {
            unset($job->foundArguments[$parameter->name]);
        }

        if ($job->foundArguments) {
            throw new UndeclaredParameters(array_keys($job->foundArguments));
        }
    }
}
