<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    ConfigOptions\DispatchDebugInformation,
    Events\DebugInformation,
    Interfaces\Serializer
};
use Medas\PdoStorage\{
    Database,
    DatabaseController,
    Events\DatabaseControllerRequest,
    Exceptions\PdoDatabase,
    Statement
};
use Medas\StorageManager\{
    Entities\LastInsertIdPlaceholder,
    Interfaces\ActionExecutor,
    UnitOfWork\Action,
    UnitOfWork\ActionSet
};

#[Service]
class QueryExecutor implements ActionExecutor
{
    /** @var DatabaseController[] The controller per database, keyed by the database's name. */
    private array $databaseControllers = [];

    public function __construct(
        #[ConfigValue(DispatchDebugInformation::class)]
        private readonly bool $dispatchDebugInformation = false,
    )
    {
    }

    public function execute(Action $action, ActionSet|null $actionSet = null): void
    {
        /** @var Database $storage */
        $storage = $action->storage();

        /** @var Query $action */
        $databaseController = $this->databaseController($storage);
        $pdo = $databaseController->pdo;

        $this->serializeArguments(
            $action,
            $databaseController->driverHandler->serializer(),
            $actionSet
        );

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

    /**
     * The controller cannot be injected: PdoStorageController depends on this class, so it
     * is asked for by event. It is asked for once per database and then kept, because this
     * runs for every statement. PdoStorageController keeps its controllers by the same key.
     */
    private function databaseController(Database $database): DatabaseController
    {
        return $this->databaseControllers[$database->name] ??= dispatch(new DatabaseControllerRequest($database))->databaseController;
    }

    /**
     * Serializes with the driver's own serializer, taken from the controller that is already
     * in hand, and not by dispatching an event per argument: a statement has many arguments
     * and a request has many statements.
     */
    private function serializeArguments(Query $query, Serializer $serializer, ActionSet|null $querySet = null): void
    {
        foreach ($query->arguments as $key => $argument) {
            if ($querySet && $argument instanceof LastInsertIdPlaceholder) {
                $argument = $querySet->lastInsertId;
            }

            $query->serializedArguments[$key] = $serializer->serialize($argument);
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
