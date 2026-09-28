<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Selector\StoreQueryBuilder;

use Medas\PdoStorage\{Database, Drivers\DriverHandler};

class Job
{
    public string $query = '';
    public array $stores = [];
    public array $outputValues = [];
    public array $foundArguments = [];
    public array $argumentOccurrences = [];
    public array $argumentLikeWrappers = [];
    public array $foundConstants = [];
    public array $variableSizedParameters = [];
    public string|null $currentCalculation = null;
    public array $aliases = [];
    public array $propertyStores = [];

    public function __construct(
        public Database        $database,
        public DriverHandler   $driverHandler,
        public readonly string $mainEntity,
    )
    {
    }

    public function storeForProperty(string|null $entity, string $propertyName): string
    {
        if ($entity !== null && $entity !== $this->mainEntity) {
            return $this->stores[$entity];
        }

        return $this->propertyStores[$propertyName] ?? $this->stores[$this->mainEntity];
    }
}
