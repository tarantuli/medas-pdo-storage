<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders\StoreQueryBuilder;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\OutputValues\{
    CalculatedValue,
    OutputValue,
    PropertyValue,
    SwitchCase
};
use Medas\PdoStorage\Exceptions\UnsupportedOutputValueType;

#[Service]
readonly class OutputValuesProcessor
{
    public function __construct(
        private OutputValueProcessor\CalculatedValueProcessor $calculatedValueProcessor,
        private OutputValueProcessor\SwitchCaseProcessor      $switchCaseProcessor,
    )
    {
    }

    /** @param OutputValue[] $outputValues */
    public function process(Job $job, array $outputValues): void
    {
        foreach ($outputValues as $outputValue) {
            $this->processOutputValue($outputValue, $job);
        }
    }

    private function processOutputValue(OutputValue $outputValue, Job $job): void
    {
        $result = match (true) {
            $outputValue instanceof CalculatedValue => $this->calculatedValueProcessor->process(
                $job,
                $outputValue,
            ),

            $outputValue instanceof SwitchCase => $this->switchCaseProcessor->process(
                $job,
                $outputValue,
            ),

            $outputValue instanceof PropertyValue => $outputValue->name,
            default => throw new UnsupportedOutputValueType($outputValue),
        };

        if ($outputValue->alias) {
            $job->aliases[] = $outputValue->alias;
            $job->outputValues[] = "$result as $outputValue->alias";
        }
        else {
            $job->outputValues[] = $result;
        }
    }
}
