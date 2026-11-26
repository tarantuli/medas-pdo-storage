<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector\StoreQueryBuilder;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\{
    Calculations\Addition,
    Calculations\Calculation,
    Calculations\Coalesce,
    Calculations\Difference,
    Calculations\Literal,
    Calculations\Multiplication,
    Calculations\RowCount,
    Conditions\OrIs,
    Conditions\WhereCollectionContains,
    Conditions\WhereContains,
    Conditions\WhereEndsWith,
    Conditions\WhereIn,
    Conditions\WhereIs,
    Conditions\WhereIsAtLeast,
    Conditions\WhereIsAtMost,
    Conditions\WhereIsLessThan,
    Conditions\WhereIsLessThanOrEqual,
    Conditions\WhereIsMoreThan,
    Conditions\WhereIsMoreThanOrEqual,
    Conditions\WhereIsNot,
    Conditions\WhereIsNotNull,
    Conditions\WhereIsNull,
    Conditions\WhereIsTruthy,
    Conditions\WhereNotIn,
    Conditions\WhereReferencedBy,
    Conditions\WhereStartsWith,
    DerivedValues\CountOf,
    DerivedValues\DerivedValue,
    DerivedValues\GroupConcatOf,
    DerivedValues\RelatedRows,
    DerivedValues\SumOf,
    DerivedValues\ValueOf,
    Exceptions\UnhandledCalculationType,
    Exceptions\UnhandledOperantType,
    Exceptions\UnhandledSortType,
    Operants\Argument,
    Operants\ArgumentArray,
    Operants\Operant,
    Operants\Property,
    Operants\Value,
    Operants\Values,
    OutputValues\OutputValue,
    OutputValues\SwitchCase,
    Sorting\SortBy
};
use Medas\PdoStorage\Exceptions\{
    CollectionContainsOperantIsNotAProperty,
    UntrustedValueUsedInSorting
};
use Medas\StorageManager\Shared\ValueSerializer;

#[Service]
readonly class CalculationsProcessor
{
    public function __construct(
        private CollectionContainsCompiler $collectionContainsCompiler,
        private ReferencedByCompiler       $referencedByCompiler,
        private RelatedRowsCompiler        $relatedRowsCompiler,
        private ValueSerializer            $valueSerializer,
    )
    {
    }

    /**
     * The SQL of a calculation on its own, leaving the job's current calculation as it
     * was: for a part written into another one, such as a derived value compared in a
     * condition, or one selected or sorted on.
     *
     * @param Calculation[]|Calculation $calculations
     */
    public function expression(Job $job, array|Calculation $calculations): string
    {
        $outer = $job->currentCalculation;

        $this->process($job, $calculations);

        $expression = $job->currentCalculation;
        $job->currentCalculation = $outer;

        return $expression;
    }

    // One part of an "order by": a property, a trusted value, or a derived value's expression
    public function sortPart(Job $job, SortBy $sort): string
    {
        $direction = ' ' . SortingProcessor::SORTING_DIRECTIONS[$sort->direction->name];

        if ($sort->operant instanceof Property) {
            return $this->operantToQuery($job, $sort->operant) . $direction;
        }

        if ($sort->operant instanceof Value) {
            if (!$sort->operant->isTrusted) {
                throw new UntrustedValueUsedInSorting($sort->operant);
            }

            return $sort->operant->value . $direction;
        }

        if ($sort->operant instanceof DerivedValue) {
            return $this->operantToQuery($job, $sort->operant) . $direction;
        }

        throw new UnhandledSortType($sort);
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
        // Unlike the aggregates, a case expression is a value of the row itself, so it
        // can be part of a condition or a derived value
        if ($calculation instanceof SwitchCase) {
            $this->processSwitchCase($job, $calculation);
        }
        elseif ($calculation instanceof OutputValue) {
            throw new UnhandledCalculationType($calculation);
        }
        else {
            match ($calculation::class) {
                WhereIsTruthy::class => $this->processTruthy($job, $calculation),
                WhereIs::class => $this->processComparison($job, $calculation, '='),
                WhereIsNot::class => $this->processComparison($job, $calculation, '!='),
                WhereIn::class => $this->processComparison($job, $calculation, ' in '),

                WhereCollectionContains::class
                    => $this->processCollectionContains($job, $calculation),

                WhereNotIn::class => $this->processComparison($job, $calculation, ' not in '),
                WhereReferencedBy::class => $this->processReferencedBy($job, $calculation),
                WhereIsMoreThan::class => $this->processComparison($job, $calculation, '>'),
                WhereIsLessThan::class => $this->processComparison($job, $calculation, '<'),

                WhereIsMoreThanOrEqual::class, WhereIsAtLeast::class
                    => $this->processComparison($job, $calculation, '>='),

                WhereIsLessThanOrEqual::class, WhereIsAtMost::class
                    => $this->processComparison($job, $calculation, '<='),

                WhereIsNull::class => $this->processNullComparison($job, $calculation, true),
                WhereIsNotNull::class => $this->processNullComparison($job, $calculation, false),
                WhereContains::class => $this->processLikeComparison($job, $calculation, '%', '%'),
                WhereStartsWith::class => $this->processLikeComparison($job, $calculation, '', '%'),
                WhereEndsWith::class => $this->processLikeComparison($job, $calculation, '%', ''),
                RowCount::class => $this->processRowCount($job),
                Literal::class => $this->processLiteral($job, $calculation),
                OrIs::class => $this->processOrIs($job, $calculation),
                Property::class => $this->processProperty($job, $calculation),

                DerivedValue::class
                    => $job->currentCalculation .= $this->operantToQuery($job, $calculation),

                CountOf::class, SumOf::class, ValueOf::class, GroupConcatOf::class
                    => $this->processRelatedRows($job, $calculation),

                Addition::class => $this->processAddition($job, $calculation),
                Multiplication::class => $this->processMultiplication($job, $calculation),
                Difference::class => $this->processDifference($job, $calculation),
                Coalesce::class => $this->processCoalesce($job, $calculation),
                default => throw new UnhandledCalculationType($calculation),
            };
        }
    }

    private function processSwitchCase(Job $job, SwitchCase $switchCase): void
    {
        $count = count($switchCase->cases);
        $output = 'case';

        for ($i = 0; $i < $count; $i += 2) {
            if (array_key_exists($i + 1, $switchCase->cases)) {
                // when X then Y
                $output .= ' when '
                    . $this->expression($job, $switchCase->cases[$i])
                    . ' then '
                    . $this->expression($job, $switchCase->cases[$i + 1]);
            }
            else {
                // else Z
                $output .= ' else ' . $this->expression($job, $switchCase->cases[$i]);
            }
        }

        $job->currentCalculation .= $output . ' end';
    }

    private function processTruthy(Job $job, WhereIsTruthy $calculation): void
    {
        $job->currentCalculation .= $this->operantToQuery($job, $calculation->property);
    }

    private function processCollectionContains(Job $job, WhereCollectionContains $calculation): void
    {
        if (!$calculation->property instanceof Property) {
            throw new CollectionContainsOperantIsNotAProperty($calculation->property);
        }

        $valueQuery = $this->operantToQuery($job, $calculation->value);

        $job->currentCalculation .= $this->collectionContainsCompiler->compile(
            $job,
            $calculation->property,
            $valueQuery,
        );
    }

    private function processReferencedBy(Job $job, WhereReferencedBy $calculation): void
    {
        $referringStore = $this->referencedByCompiler->referringStore($job, $calculation);

        $job->currentCalculation .= $this->referencedByCompiler->open(
            $job,
            $calculation,
            $referringStore
        );

        // While the subquery's conditions are written, the referring entity's
        // properties resolve to its aliased store; an outer join of the same
        // entity gets its own store back afterwards.
        $hadOuterStore = array_key_exists($calculation->referringEntity, $job->stores);
        $outerStore = $job->stores[$calculation->referringEntity] ?? null;
        $job->stores[$calculation->referringEntity] = $referringStore;

        try {
            foreach ($calculation->conditions as $condition) {
                $job->currentCalculation .= ' and ';

                $this->processCalculation($job, $condition);
            }
        }
        finally {
            if ($hadOuterStore) {
                $job->stores[$calculation->referringEntity] = $outerStore;
            }
            else {
                unset($job->stores[$calculation->referringEntity]);
            }
        }

        $job->currentCalculation .= ')';
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
        elseif ($calculation->value instanceof Argument && ($prefix !== '' || $suffix !== '')) {
            $job->argumentLikeWrappers[$calculation->value->name] = [
                'prefix' => $prefix,
                'suffix' => $suffix,
            ];
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

    private function processRowCount(Job $job): void
    {
        $job->currentCalculation .= '*';
    }

    // A whole number is written into the query as it is. Bound, it would reach MySQL as
    // text (the statement binds every argument as a string), and coalesce(sum(...), '0')
    // is then text as well, so it sorts and compares as text: "9000" >= "100000". A
    // literal comes from code, never from a request, and an int cannot carry anything
    // but digits.
    private function processLiteral(Job $job, Literal $calculation): void
    {
        if (is_int($calculation->literal)) {
            $job->currentCalculation .= (string) $calculation->literal;

            return;
        }

        $job->currentCalculation .= $this->addValue($calculation->literal, $job);
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

    /**
     * A bare property, e.g. the argument of an aggregate such as Max
     */
    private function processProperty(Job $job, Property $calculation): void
    {
        $job->currentCalculation .= $this->operantToQuery($job, $calculation);
    }

    private function operantToQuery(Job $job, Operant $operant, bool|null &$addOrIsNull = null): string
    {
        if ($operant instanceof Property) {
            return $job->storeForProperty($operant->entity, $operant->name)
                . '.'
                . $job->driverHandler->quote($job->database, $operant->name);
        }

        if ($operant instanceof Argument) {
            $job->foundArguments[$operant->name] = true;
            $occurrence = $job->argumentOccurrences[$operant->name] ?? 0;
            $job->argumentOccurrences[$operant->name] = $occurrence + 1;

            // The first occurrence keeps the plain name; repeats get a suffix so the same
            // logical argument can be bound to multiple distinct placeholders under native
            // (non-emulated) prepares, which don't allow a placeholder name to repeat.
            $placeholderName = $occurrence === 0
                ? $operant->name
                : $operant->name . '__' . $occurrence;

            return ':' . $placeholderName;
        }

        // In parentheses, as it can be a case expression or a difference
        if ($operant instanceof DerivedValue) {
            return '(' . $this->expression($job, $operant->calculation) . ')';
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

    private function addValue(mixed &$value, Job $job): string
    {
        $value = $this->valueSerializer->serialize($value);
        $name = 'c' . count($job->foundConstants);
        $job->foundConstants[$name] = $value;

        return ':' . $name;
    }

    private function processRelatedRows(Job $job, RelatedRows $rows): void
    {
        $subJob = $this->relatedRowsCompiler->open($job, $rows);
        $selected = match (true) {
            $rows instanceof CountOf => 'count(*)',
            $rows instanceof SumOf => 'sum(' . $this->expression($subJob, $rows->value) . ')',
            $rows instanceof ValueOf => $this->expression($subJob, $rows->value),
            $rows instanceof GroupConcatOf => $this->groupConcat($subJob, $rows),
            default => throw new UnhandledCalculationType($rows),
        };

        $query = '(select ' . $selected . $subJob->query;

        if ($rows->definition->conditions) {
            $query .= ' and ' . $this->expression($subJob, $rows->definition->conditions);
        }

        // The order only decides which row is the first; a count or sum is the same in any order
        if ($rows instanceof ValueOf) {
            $parts = array_map(
                fn(SortBy $sort) => $this->sortPart($subJob, $sort),
                $rows->definition->sorts
            );

            if ($parts) {
                $query .= ' order by ' . implode(', ', $parts);
            }

            $query .= ' limit 1';
        }

        $this->relatedRowsCompiler->close($job, $subJob);

        $job->currentCalculation .= $query . ')';
    }

    // MySQL's syntax. The order goes inside the aggregate, as it decides the order of the
    // joined values; the separator is a literal, as it cannot be a placeholder there.
    private function groupConcat(Job $subJob, GroupConcatOf $rows): string
    {
        $parts = array_map(
            fn(SortBy $sort) => $this->sortPart($subJob, $sort),
            $rows->definition->sorts
        );

        return 'group_concat('
            . ($rows->isDistinct ? 'distinct ' : '')
            . $this->expression($subJob, $rows->value)
            . ($parts ? ' order by ' . implode(', ', $parts) : '')
            . ' separator '
            . $subJob->driverHandler->escape($subJob->database, $rows->separator)
            . ')';
    }

    private function processAddition(Job $job, Addition $calculation): void
    {
        $terms = array_map(
            fn(Calculation $term) => $this->expression($job, $term),
            $calculation->terms
        );

        $job->currentCalculation .= '(' . implode(' + ', $terms) . ')';
    }

    private function processMultiplication(Job $job, Multiplication $calculation): void
    {
        $factors = array_map(
            fn(Calculation $factor) => $this->expression($job, $factor),
            $calculation->factors
        );

        $job->currentCalculation .= '(' . implode(' * ', $factors) . ')';
    }

    private function processDifference(Job $job, Difference $calculation): void
    {
        $job->currentCalculation .= '('
            . $this->expression($job, $calculation->minuend)
            . ' - '
            . $this->expression($job, $calculation->subtrahend)
            . ')';
    }

    private function processCoalesce(Job $job, Coalesce $calculation): void
    {
        $parts = array_map(
            fn(Calculation $part) => $this->expression($job, $part),
            $calculation->calculations
        );

        $job->currentCalculation .= 'coalesce(' . implode(', ', $parts) . ')';
    }
}
