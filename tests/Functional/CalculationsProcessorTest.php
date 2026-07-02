<?php

declare(strict_types=1);

namespace Medas\PdoStorageTest\Functional;

use Medas\EntityManager\Selector\{
    Conditions\OrIs,
    Conditions\WhereIs,
    Conditions\WhereIsAtLeast,
    Conditions\WhereIsAtMost,
    Conditions\WhereIsNull,
    Operants\Argument,
    Operants\Property,
    Operants\Value
};
use Medas\PdoStorage\Queries\Selector\StoreQueryBuilder\{CalculationsProcessor, Job};
use Medas\PdoStorageTest\MockUps\{MockUpDatabase, MockupDriverHandler};
use PHPUnit\Framework\TestCase;

class CalculationsProcessorTest extends TestCase
{
    public function testBasicQuery(): void
    {
        $job = $this->createJob();
        $processor = service(CalculationsProcessor::class);

        $processor->process($job, [WhereIs::c(Property::c('name'), Argument::c('name'))]);

        self::assertEquals('entities.`name`=:name', $job->currentCalculation);
        self::assertEquals(['name' => true], $job->foundArguments);
    }

    public function testTwoOperants(): void
    {
        $job = $this->createJob();
        $processor = service(CalculationsProcessor::class);

        $processor->process($job, [
            WhereIs::c(Property::c('name'), Argument::c('name')),
            WhereIsAtLeast::c(Property::c('age'), Argument::c('age')),
        ]);

        self::assertEquals(
            'entities.`name`=:name and entities.`age`>=:age',
            $job->currentCalculation
        );

        self::assertEquals(['name' => true, 'age' => true], $job->foundArguments);
    }

    public function testNull(): void
    {
        $job = $this->createJob();
        $processor = service(CalculationsProcessor::class);

        $processor->process($job, [
            WhereIs::c(Property::c('name'), Argument::c('name')),
            WhereIsNull::c(Property::c('age')),
        ]);

        self::assertEquals(
            'entities.`name`=:name and entities.`age` is null',
            $job->currentCalculation
        );

        self::assertEquals(['name' => true], $job->foundArguments);
    }

    public function testOrIs(): void
    {
        $job = $this->createJob();
        $processor = service(CalculationsProcessor::class);

        $processor->process(
            $job,
            [
                WhereIs::c(Property::c('name'), Argument::c('name')),
                OrIs::c(
                    WhereIs::c(Property::c('country'), Value::c('The Netherlands')),
                    WhereIs::c(Property::c('country'), Value::c('Belgium'))
                )
            ]
        );

        self::assertEquals(
            'entities.`name`=:name and (entities.`country`=:c0 or entities.`country`=:c1)',
            $job->currentCalculation
        );

        self::assertEquals(['name' => true], $job->foundArguments);
        self::assertEquals(['c0' => 'The Netherlands', 'c1' => 'Belgium'], $job->foundConstants);
    }

    public function testOrIsNull(): void
    {
        $job = $this->createJob();
        $processor = service(CalculationsProcessor::class);

        $processor->process(
            $job,
            [
                WhereIs::c(Property::c('name'), Argument::c('name')),
                OrIs::null(WhereIs::c(Property::c('country'), Value::c('The Netherlands')))
            ]
        );

        self::assertEquals(
            'entities.`name`=:name and (entities.`country`=:c0 or entities.`country` is null)',
            $job->currentCalculation
        );

        self::assertEquals(['name' => true], $job->foundArguments);
        self::assertEquals(['c0' => 'The Netherlands'], $job->foundConstants);
    }

    public function testMultipleArgumentCalls(): void
    {
        $job = $this->createJob();
        $processor = service(CalculationsProcessor::class);

        $processor->process($job, [
            WhereIsAtLeast::c(Property::c('startDate'), Argument::c('now')),
            OrIs::null(WhereIsAtMost::c(Property::c('endDate'), Argument::c('now'))),
        ]);

        // The query must contain a deduplicated entry for :now,
        // but the foundArguments should only reveal the user-supplied argument name
        self::assertEquals(
            'entities.`startDate`>=:now and (entities.`endDate`<=:now__1 or entities.`endDate` is null)',
            $job->currentCalculation
        );

        self::assertEquals(['now' => true], $job->foundArguments);
    }

    private function createJob(): Job
    {
        $job = new Job(new MockUpDatabase(), new MockupDriverHandler(), 'Entity');

        $job->stores['Entity'] = 'entities';

        return $job;
    }
}
