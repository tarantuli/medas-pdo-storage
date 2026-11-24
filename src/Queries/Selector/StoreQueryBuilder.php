<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\Definition;
use Medas\PdoStorage\{
    Database,
    Events\DatabaseControllerRequest,
    Exceptions\DerivedValuesWithOutputValues
};

#[Service]
readonly class StoreQueryBuilder
{
    public function __construct(
        private StoreQueryBuilder\CalculationsProcessor $calculationsProcessor,
        private StoreQueryBuilder\GroupingProcessor     $groupingProcessor,
        private StoreQueryBuilder\OutputValuesProcessor $outputValuesProcessor,
        private StoreQueryBuilder\ParametersProcessor   $parametersProcessor,
        private StoreQueryBuilder\RelationsProcessor    $relationsProcessor,
        private StoreQueryBuilder\SliceProcessor        $sliceProcessor,
        private StoreQueryBuilder\SortingProcessor      $sortingProcessor,
    )
    {
    }

    public function buildQuery(
        Database   $database,
        Definition $definition,
        string     $storeName,
        string     $entityName = '',
        bool       $isCount = false,
        array      $parentStores = [],
        array      $propertyStores = [],
        string     $idProperty = 'id'
    ): ParameterizedQuery
    {
        $controllerRequest = dispatch(new DatabaseControllerRequest($database));

        $job = new StoreQueryBuilder\Job(
            $database,
            $controllerRequest->databaseController->driverHandler,
            $entityName,
        );

        $quotedMainStore = $job->driverHandler->quote($database, $storeName);
        $job->stores = [$job->mainEntity => $quotedMainStore];

        $inheritanceJoins = $this->applyInheritanceStores(
            $database,
            $job,
            $quotedMainStore,
            $parentStores,
            $propertyStores,
            $idProperty
        );

        // The output values may name properties of joined entities, but the joins are only written after them
        $this->relationsProcessor->registerStores($job, $definition->relations);
        $this->outputValuesProcessor->process($job, $definition->outputValues);

        if ($job->outputValues && $definition->derivedValues) {
            throw new DerivedValuesWithOutputValues($entityName);
        }

        if ($isCount) {
            $outputValues = 'count(*) as ' . $job->driverHandler->quote($database, 'count');
        }
        elseif ($job->outputValues) {
            $outputValues = implode(', ', $job->outputValues);
        }

        // A joined entity's columns would mix with the entity's own, and where names
        // are the same (the id, the timestamps), the joined one's value would win.
        // Derived values go beside the entity's own columns, under their own names.
        elseif ($definition->relations || $definition->derivedValues) {
            $outputValues = $this->ownColumns($database, $job, $quotedMainStore, $parentStores)
                . $this->derivedColumns($database, $job, $definition);
        }
        else {
            $outputValues = '*';
        }

        $job->query = sprintf('select %s from %s', $outputValues, $quotedMainStore);
        $job->query .= $inheritanceJoins;

        $this->relationsProcessor->process($job, $definition->relations);

        if ($definition->conditions) {
            $this->calculationsProcessor->process($job, $definition->conditions);

            $job->query .= ' where ' . $job->currentCalculation;
        }

        $this->groupingProcessor->process($job, $definition->groupings);

        // The order does not change a count, and a sort on a derived value would have it
        // worked out for every row for nothing
        if (!$isCount) {
            $this->sortingProcessor->process($job, $definition->sorts);
        }

        $usedParameters = $this->parametersProcessor->process($job, $definition->parameters);

        if (!$isCount) {
            $this->sliceProcessor->process($job, $definition->slice);
        }

        return new ParameterizedQuery(
            $job->query,
            $usedParameters,
            $job->foundConstants,
            $job->variableSizedParameters,
            $job->argumentOccurrences,
            $job->argumentLikeWrappers,
            $database,
        );
    }

    private function applyInheritanceStores(
        Database              $database,
        StoreQueryBuilder\Job $job,
        string                $quotedMainStore,
        array                 $parentStores,
        array                 $propertyStores,
        string                $idProperty
    ): string
    {
        foreach ($propertyStores as $propertyName => $storeName) {
            $job->propertyStores[$propertyName] = $job->driverHandler->quote($database, $storeName);
        }

        if (!$parentStores) {
            return '';
        }

        $quotedId = $job->driverHandler->quote($database, $idProperty);
        $joins = '';

        foreach ($parentStores as $parentStore) {
            $quotedParent = $job->driverHandler->quote($database, $parentStore);

            $joins .= ' join '
                . $quotedParent
                . ' on ('
                . $quotedParent
                . '.'
                . $quotedId
                . ' = '
                . $quotedMainStore
                . '.'
                . $quotedId
                . ')';
        }

        return $joins;
    }

    /** @param string[] $parentStores */
    private function ownColumns(
        Database              $database,
        StoreQueryBuilder\Job $job,
        string                $quotedMainStore,
        array                 $parentStores
    ): string
    {
        $columns = [$quotedMainStore . '.*'];

        foreach ($parentStores as $parentStore) {
            $columns[] = $job->driverHandler->quote($database, $parentStore) . '.*';
        }

        return implode(', ', $columns);
    }

    // ", <expression> as <name>" per derived value. In a condition or a sort the expression is
    // written again, not the name: neither the "where" nor the count query can use it.
    private function derivedColumns(Database $database, StoreQueryBuilder\Job $job, Definition $definition): string
    {
        $columns = '';

        foreach ($definition->derivedValues as $derivedValue) {
            $columns .= ', '
                . $this->calculationsProcessor->expression($job, $derivedValue->calculation)
                . ' as '
                . $job->driverHandler->quote($database, $derivedValue->name);
        }

        return $columns;
    }

    public function buildCountQuery(
        Database   $database,
        Definition $definition,
        string     $storeName,
        string     $entityName = '',
    ): ParameterizedQuery
    {
        $request = dispatch(new DatabaseControllerRequest($database));

        $job = new StoreQueryBuilder\Job(
            $database,
            $request->databaseController->driverHandler,
            $entityName,
        );

        $quotedMainStore = $job->driverHandler->quote($database, $storeName);
        $job->stores = [$job->mainEntity => $quotedMainStore];
        $job->query = 'select count(*) as rowCount from ' . $quotedMainStore;

        $this->relationsProcessor->process($job, $definition->relations);

        if ($definition->conditions) {
            $this->calculationsProcessor->process($job, $definition->conditions);

            $job->query .= ' where ' . $job->currentCalculation;
        }

        $usedParameters = $this->parametersProcessor->process($job, $definition->parameters);

        return new ParameterizedQuery(
            $job->query,
            $usedParameters,
            $job->foundConstants,
            $job->variableSizedParameters,
            $job->argumentOccurrences,
            $job->argumentLikeWrappers,
            $database,
        );
    }
}
