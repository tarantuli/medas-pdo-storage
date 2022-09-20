<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\PdoStorage\Exceptions\PdoDatabaseException;
use Medas\PdoStorage\Queries\{Query, SelectQueryBuilder};
use Medas\PdoStorage\Structure\{TableMigrationBuilder, TypeHandlerFinder};
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Entities\{SelectorActionBuilder, TypeSerializerFinder};
use Medas\StorageManager\Interfaces\StorageController;
use Medas\StorageManager\Migrations\MigrationBuilder;

#[Service]
class DatabaseController implements StorageController
{
    public function __construct(
        private readonly TableMigrationBuilder $migrationBuilder,
        private readonly TypeHandlerFinder     $typeHandlerFinder,
        private readonly SelectQueryBuilder    $selectQueryBuilder,
    )
    {
    }

    public function execute(Database $database, \PDO $pdo, Query $query): void
    {
        $this->serializeArguments($query);

        try {
            $statement = $pdo->prepare($query->query);
            $statement->execute($query->serializedArguments);

            $query->setStatement(new Statement($statement));
        }
        catch (\Exception|\Error $e) {
            throw new PdoDatabaseException($e->getMessage(), $query);
        }

        if ($onComplete = $query->onComplete()) {
            $onComplete($database);
        }
    }

    private function serializeArguments(Query $query): void
    {
        foreach ($query->arguments as $argument) {
            if ($argument instanceof \DateTime) {
                $argument = $argument->format('Y-m-d H:i:s');
            }

            if (is_bool($argument)) {
                $argument = (int) $argument;
            }

            $query->serializedArguments[] = $argument;
        }
    }

    public function migrationBuilder(): MigrationBuilder
    {
        return $this->migrationBuilder;
    }

    public function typeSerializerFinder(): TypeSerializerFinder
    {
        return $this->typeHandlerFinder;
    }

    public function selectorActionBuilder(): SelectorActionBuilder
    {
        return $this->selectQueryBuilder;
    }
}
