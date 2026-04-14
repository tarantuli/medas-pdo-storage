<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders\StoreQueryBuilder\OutputValueProcessor;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\OutputValues\SwitchCase;
use Medas\PdoStorage\Queries\Builders\StoreQueryBuilder\{CalculationsProcessor, Job};

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
        $count = count($switchCase->cases);
        $output = 'case';

        for ($i = 0; $i < $count; $i += 2) {
            if (array_key_exists($i + 1, $switchCase->cases)) {
                // when X then Y
                $this->calculationsProcessor->process($job, $switchCase->cases[$i]);

                $when = $job->currentCalculation;

                $this->calculationsProcessor->process($job, $switchCase->cases[$i + 1]);

                $then = $job->currentCalculation;
                $output .= " when $when then $then";
            }
            else {
                // else Z
                $this->calculationsProcessor->process($job, $switchCase->cases[$i]);

                $else = $job->currentCalculation;
                $output .= " else $else";
            }
        }

        $output .= ' end';

        return $output;
    }
}
