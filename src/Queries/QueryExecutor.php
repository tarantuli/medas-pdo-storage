<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\Exceptions\PdoDatabase;
use Medas\PdoStorage\PdoStorageController;
use Medas\PdoStorage\Statement;
use Medas\PdoStorage\ValueSerializer;
use Medas\StorageManager\Entities\LastInsertIdPlaceholder;
use Medas\StorageManager\Interfaces\ActionExecutor;
use Medas\StorageManager\UnitOfWork\Action;
use Medas\StorageManager\UnitOfWork\ActionSet;

#[Service]
readonly class QueryExecutor implements ActionExecutor
{
    public function __construct(
        private PdoStorageController $pdoStorageController,
    )
    {
    }

    public function execute(Action $action): void
    {
        $this->executeQuery($this->pdoStorageController->getDatabaseController($action->storage())->pdo, $action);
    }

    public function executeSet(ActionSet $actionSet): void
    {
        foreach ($actionSet as $action) {
            $this->execute($action);
        }
    }

    public function executeQuery(\PDO $pdo, Query $query): void
    {
        $lastInsertId = $this->serializeArguments($pdo, $query);

        try {
            $statement = $pdo->prepare($query->query);
            $statement->execute($query->serializedArguments);

            $query->setStatement(new Statement($statement));
        }
        catch (\Exception|\Error $e) {
            throw new PdoDatabase($e->getMessage(), $query);
        }

        if ($onComplete = $query->onComplete()) {
            $onComplete($query->storage(), $lastInsertId);
        }
    }

    private function serializeArguments(\PDO $pdo, Query $query): int|null
    {
        $lastInsertId = null;
        $serializer = service(ValueSerializer::class);

        foreach ($query->arguments as $key => $argument) {
            if ($argument instanceof LastInsertIdPlaceholder) {
                $lastInsertId = $argument = (int) $pdo->lastInsertId();
            }

            $query->serializedArguments[$key] = $serializer->serialize($argument);
        }

        return $lastInsertId;
    }
}
