<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector\StoreQueryBuilder;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\{
    Calculations\Calculation,
    Calculations\Literal,
    Calculations\RowCount,
    Conditions\OrIs,
    Conditions\WhereContains,
    Conditions\WhereEndsWith,
    Conditions\WhereIn,
    Conditions\WhereIs,
    Conditions\WhereIsAtLeast,
    Conditions\WhereIsAtMost,
    Conditions\WhereIsLessThan,
    Conditions\WhereIsMoreThan,
    Conditions\WhereIsNot,
    Conditions\WhereIsNotNull,
    Conditions\WhereIsNull,
    Conditions\WhereIsTruthy,
    Conditions\WhereNotIn,
    Conditions\WhereStartsWith,
    Exceptions\UnhandledCalculationType,
    Exceptions\UnhandledOperantType,
    Operants\Argument,
    Operants\ArgumentArray,
    Operants\Operant,
    Operants\Property,
    Operants\Value,
    Operants\Values,
    OutputValues\OutputValue
};
use Medas\StorageManager\Shared\ValueSerializer;

#[Service]
readonly class CalculationsProcessor
{
    public function __construct(
        private ValueSerializer $valueSerializer,
    )
    {
    }

    /** @param Calculation[]|Calculation $calculations */
    public function process(Job $job, array|Calculation $calculations): void
    {
        if (!is_array($calculations)) {
            $calculations = [$calculations];
        }

        $job->currentCalculation = $calculations ? '' : null;
        $isFirstCalculation = true;

        foreach ($calculations as $calculation) {
            if (!$isFirstCalculation) {
                $job->currentCalculation .= ' and ';
            }

            $this->processCalculation($job, $calculation);

            $isFirstCalculation = false;
        }
    }

    private function processCalculation(Job $job, Calculation $calculation): void
    {
        if ($calculation instanceof OutputValue) {
            throw new UnhandledCalculationType($calculation);
        }
        else {
            match ($calculation::class) {
                WhereIsTruthy::class => $this->processTruthy($job, $calculation),
                WhereIs::class => $this->processComparison($job, $calculation, '='),
                WhereIsNot::class => $this->processComparison($job, $calculation, '!='),
                WhereIn::class => $this->processComparison($job, $calculation, ' in '),
                WhereNotIn::class => $this->processComparison($job, $calculation, ' not in '),
                WhereIsMoreThan::class => $this->processComparison($job, $calculation, '>'),
                WhereIsLessThan::class => $this->processComparison($job, $calculation, '<'),
                WhereIsAtLeast::class => $this->processComparison($job, $calculation, '>='),
                WhereIsAtMost::class => $this->processComparison($job, $calculation, '<='),
                WhereIsNull::class => $this->processNullComparison($job, $calculation, true),
                WhereIsNotNull::class => $this->processNullComparison($job, $calculation, false),
                WhereContains::class => $this->processLikeComparison($job, $calculation, '%', '%'),
                WhereStartsWith::class => $this->processLikeComparison($job, $calculation, '', '%'),
                WhereEndsWith::class => $this->processLikeComparison($job, $calculation, '%', ''),
                RowCount::class => $this->processRowCount($job),
                Literal::class => $this->processLiteral($job, $calculation),
                OrIs::class => $this->processOrIs($job, $calculation),
                default => throw new UnhandledCalculationType($calculation),
            };
        }
    }

    private function processTruthy(Job $job, WhereIsTruthy $calculation): void
    {
        $job->currentCalculation .= $this->operantToQuery($job, $calculation->property);
    }

    private function processNullComparison(Job $job, WhereIsNull $calculation, bool $isNull): void
    {
        $job->currentCalculation .= $this->operantToQuery($job, $calculation->property)
            . ($isNull ? ' is null' : ' is not null');
    }

    private function processLikeComparison(Job $job, WhereIs $calculation, string $prefix, string $suffix): void
    {
        if ($calculation->value instanceof Value) {
            $likeValue = new Value($prefix . $calculation->value->value . $suffix);
            $calculation = new WhereIs($calculation->property, $likeValue);
        }

        $this->processComparison($job, $calculation, ' like ');
    }

    private function processComparison(Job $job, WhereIs $calculation, string $operator): void
    {
        $nameQuery = $this->operantToQuery($job, $calculation->property);
        $addOrIsNull = false;
        $valueQuery = $this->operantToQuery($job, $calculation->value, $addOrIsNull);
        $baseQuery = $nameQuery . $operator . $valueQuery;

        if ($addOrIsNull) {
            $job->currentCalculation .= '(' . $baseQuery . ' or ' . $nameQuery . ' is null)';
        }
        else {
            $job->currentCalculation .= $baseQuery;
        }
    }

    private function operantToQuery(Job $job, Operant $operant, bool|null &$addOrIsNull = null): string
    {
        if ($operant instanceof Property) {
            return $job->stores[$operant->entity ?? $job->mainEntity]
                . '.'
                . $job->driverHandler->quote($job->database, $operant->name);
        }

        if ($operant instanceof Argument) {
            $job->foundArguments[$operant->name] = true;

            return ':' . $operant->name;
        }

        if ($operant instanceof ArgumentArray) {
            $job->foundArguments[$operant->name] = true;
            $job->variableSizedParameters[$operant->name] = true;

            return '(:' . $operant->name . ')';
        }

        if ($operant instanceof Value) {
            return $this->addValue($operant->value, $job);
        }

        if ($operant instanceof Values) {
            $names = [];

            foreach ($operant->value as $value) {
                if ($value === null) {
                    $addOrIsNull = true;
                }
                else {
                    $names[] = $this->addValue($value, $job);
                }
            }

            return '(' . implode(',', $names) . ')';
        }

        throw new UnhandledOperantType($operant);
    }

    private function processRowCount(Job $job): void
    {
        $job->currentCalculation = '*';
    }

    private function processLiteral(Job $job, Literal $calculation): void
    {
        $job->currentCalculation = $this->addValue($calculation->literal, $job);
    }

    private function addValue(mixed &$value, Job $job): string
    {
        $value = $this->valueSerializer->serialize($value);
        $name = 'c' . count($job->foundConstants);
        $job->foundConstants[$name] = $value;

        return ':' . $name;
    }

    private function processOrIs(Job $job, OrIs $calculation): void
    {
        $job->currentCalculation .= '(';
        $isFirstCalculation = true;

        foreach ($calculation->conditions as $condition) {
            if (!$isFirstCalculation) {
                $job->currentCalculation .= ' or ';
            }

            $this->processCalculation($job, $condition);

            $isFirstCalculation = false;
        }

        $job->currentCalculation .= ')';
    }
}
