<?php

declare(strict_types=1);

namespace Medas\PdoStorageTest\Functional;

use Medas\Core\Interfaces\NotCacheable;
use Medas\EntityManager\Exceptions\{GroupingsThroughRepository, OutputValuesThroughRepository};
use Medas\EntityManager\Selector\{
    Definition,
    Element,
    Grouping\GroupBy,
    Operants\Property,
    OutputValues\Count,
    OutputValues\PropertyValue,
    Selector
};
use Medas\PdoStorage\Queries\Selector\SelectorQueryBuilder;
use PHPUnit\Framework\TestCase;

// This builds the queries of Repository::fetch() and fetchCount(), which turn every row into
// an entity, so a selector whose rows are not the entity's own is refused before anything else
// (#398). That derived values still pass is tested where there is a database: the lists of the
// Overs8 backend sort and filter on them.
class SelectorQueryBuilderTest extends TestCase
{
    public const string ENTITY = 'Entity';

    public function testAFetchWithOutputValuesIsRefused(): void
    {
        $this->expectException(OutputValuesThroughRepository::class);

        service(SelectorQueryBuilder::class)->build(
            $this->selectorWith(PropertyValue::c('name')),
            []
        );
    }

    public function testACountWithOutputValuesIsRefused(): void
    {
        $this->expectException(OutputValuesThroughRepository::class);

        service(SelectorQueryBuilder::class)->build(
            $this->selectorWith(Count::c(Property::c('id'))->as('count')),
            [],
            doCount: true
        );
    }

    public function testAFetchWithGroupingsIsRefused(): void
    {
        $this->expectException(GroupingsThroughRepository::class);

        service(SelectorQueryBuilder::class)->build(
            $this->selectorWith(GroupBy::c(Property::c('name'))),
            []
        );
    }

    // The case that gave a wrong total: a count per group, of which the first was taken
    public function testACountWithGroupingsIsRefused(): void
    {
        $this->expectException(GroupingsThroughRepository::class);

        service(SelectorQueryBuilder::class)->build(
            $this->selectorWith(GroupBy::c(Property::c('name'))),
            [],
            doCount: true
        );
    }

    public function testTheMessageNamesTheEntityAndTheStoreLevelQuery(): void
    {
        try {
            service(SelectorQueryBuilder::class)->build(
                $this->selectorWith(PropertyValue::c('name')),
                []
            );

            self::fail('The output value was not refused');
        }
        catch (OutputValuesThroughRepository $exception) {
            self::assertStringContainsString(
                'a selector of ' . self::ENTITY,
                $exception->getMessage()
            );

            self::assertStringContainsString('DefinitionQueryBuilder', $exception->getMessage());
        }
    }

    // Not cacheable, as every selector made here has the same class but other elements
    private function selectorWith(Element ...$elements): Selector
    {
        return new class($elements) implements Selector, NotCacheable {
            /** @param Element[] $elements */
            public function __construct(private readonly array $elements)
            {
            }

            public function definition(): Definition
            {
                return Definition::create(SelectorQueryBuilderTest::ENTITY)->add(...$this->elements);
            }

            public function entity(): string
            {
                return SelectorQueryBuilderTest::ENTITY;
            }
        };
    }
}
