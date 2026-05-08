<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    ConfigOptions\DispatchDebugInformation,
    Events\DebugInformation
};
use Medas\PdoStorage\{Events\DatabaseControllerRequest, Exceptions\PdoDatabase, Statement};
use Medas\StorageManager\{
    Entities\LastInsertIdPlaceholder,
    Interfaces\ActionExecutor,
    Shared\SerializeValueRequest,
    UnitOfWork\Action,
    UnitOfWork\ActionSet
};

#[Service]
readonly class QueryExecutor implements ActionExecutor
{
    public function __construct(
        #[ConfigValue(DispatchDebugInformation::class)]
        private bool $dispatchDebugInformation = false,
    )
    {
    }

    public function execute(Action $action, ActionSet|null $actionSet = null): void
    {
        /** @var Query $action */
        $request = dispatch(new DatabaseControllerRequest($action->storage()));
        $databaseController = $request->databaseController;
        $pdo = $databaseController->pdo;

        $this->serializeArguments($action, $actionSet);

        try {
            $statement = $pdo->prepare($action->query);

            if ($this->dispatchDebugInformation) {
                dispatch(new DebugInformation(
                    '[pdo-storage] executing query %s with arguments %s',
                    $action->query,
                    $action->serializedArguments
                ));
            }

            $statement->execute($action->serializedArguments);

            $lastInsertId = $pdo->lastInsertId();

            if ($lastInsertId && $actionSet) {
                $actionSet->lastInsertId = $lastInsertId;
            }

            $action->statement = new Statement($statement);
        }
        catch (\Exception|\Error $e) {
            throw new PdoDatabase(
                $e->getMessage(),
                $action,
                $e,
                $databaseController->driverHandler->exceptionTypeFinder()->find($e)
            );
        }

        if ($onComplete = $action->onComplete()) {
            $onComplete($action->storage(), $actionSet ? $actionSet->lastInsertId : $lastInsertId);
        }
    }

    private function serializeArguments(Query $query, ActionSet|null $querySet = null): void
    {
        foreach ($query->arguments as $key => $argument) {
            if ($querySet && $argument instanceof LastInsertIdPlaceholder) {
                $argument = $querySet->lastInsertId;
            }

            $request = dispatch(new SerializeValueRequest($argument));
            $query->serializedArguments[$key] = $request->serializedValue;
        }
    }

    public function executeSet(ActionSet $actionSet): void
    {
        foreach ($actionSet as $action) {
            $this->execute($action, $actionSet);

            $actionSet->lastRecordSet = $action->recordSet();
        }
    }
}
