<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\PdoStorage\Exceptions\PdoDatabase;
use Medas\PdoStorage\Queries\Query;
use Medas\StorageManager\Entities\LastInsertIdPlaceholder;

class Executor
{
    public function execute(\PDO $pdo, Query $query): void
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
