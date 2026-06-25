<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector\StoreQueryBuilder\OutputValueProcessor;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\OutputValues\{CalculatedValue, Count, Sum};
use Medas\PdoStorage\Exceptions\UnsupportedOutputValueType;
use Medas\PdoStorage\Queries\Selector\StoreQueryBuilder\{CalculationsProcessor, Job};

#[Service]
readonly class CalculatedValueProcessor
{
    private const array TYPE_MAPPING = [
        CalculatedValue::class => '',
        Count::class => 'count',
        Sum::class => 'sum',
    ];

    public function __construct(
        private CalculationsProcessor $calculationsProcessor,
    )
    {
    }

    public function process(Job $job, CalculatedValue $outputValue): string
    {
        if (!array_key_exists($outputValue::class, self::TYPE_MAPPING)) {
            throw new UnsupportedOutputValueType($outputValue);
        }

        $this->calculationsProcessor->process($job, $outputValue->calculations);

        return sprintf('%s(%s)', self::TYPE_MAPPING[$outputValue::class], $job->currentCalculation);
    }
}
