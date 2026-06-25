<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\Definition;
use Medas\PdoStorage\{Database, Events\DatabaseControllerRequest};

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

        $this->outputValuesProcessor->process($job, $definition->outputValues);

        $outputValues = $job->outputValues ? implode(', ', $job->outputValues) : '*';
        $job->query = sprintf('select %s from %s', $outputValues, $quotedMainStore);

        $this->relationsProcessor->process($job, $definition->relations);

        if ($definition->conditions) {
            $this->calculationsProcessor->process($job, $definition->conditions);

            $job->query .= ' where ' . $job->currentCalculation;
        }

        $this->groupingProcessor->process($job, $definition->groupings);
        $this->sortingProcessor->process($job, $definition->sorts);
        $this->parametersProcessor->process($job, $definition->parameters);
        $this->sliceProcessor->process($job, $definition->slice);

        return new ParameterizedQuery(
            $job->query,
            $definition->parameters,
            $job->foundConstants,
            $job->variableSizedParameters,
            $database,
        );
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

        $this->parametersProcessor->process($job, $definition->parameters);

        return new ParameterizedQuery(
            $job->query,
            $definition->parameters,
            $job->foundConstants,
            $job->variableSizedParameters,
            $database,
        );
    }
}
