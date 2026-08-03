<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector\StoreQueryBuilder;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\Selector\{
    Exceptions\UndeclaredParameters,
    Exceptions\UnusedParameters,
    Parameter
};
use Medas\PdoStorage\ConfigOptions\ThrowOnUnusedParameters;

#[Service]
readonly class ParametersProcessor
{
    public function __construct(
        #[ConfigValue(ThrowOnUnusedParameters::class)]
        private bool $throwOnUnusedParameters,
    )
    {
    }

    /**
     * @param  Parameter[] $parameters
     * @return Parameter[] the declared parameters actually referenced in the
     *                     query. A declared-but-unreferenced parameter (e.g. a
     *                     pure gate whose presence selects a query branch but
     *                     which no condition binds) is dropped, so it never
     *                     gets bound to a placeholder that doesn't exist. With
     *                     ThrowOnUnusedParameters on, such a parameter instead
     *                     raises UnusedParameters - unless it's marked
     *                     allowUnused, which exempts deliberate gates.
     */
    public function process(Job $job, array $parameters): array
    {
        $used = [];
        $unused = [];

        foreach ($parameters as $parameter) {
            if (isset($job->foundArguments[$parameter->name])) {
                $used[] = $parameter;
            }
            elseif (!$parameter->allowUnused) {
                $unused[] = $parameter->name;
            }

            unset($job->foundArguments[$parameter->name]);
        }

        if ($job->foundArguments) {
            throw new UndeclaredParameters(array_keys($job->foundArguments));
        }

        if ($this->throwOnUnusedParameters && $unused) {
            throw new UnusedParameters($unused);
        }

        return $used;
    }
}
