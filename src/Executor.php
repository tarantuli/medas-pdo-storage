<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\PdoStorage\Exceptions\PdoDatabase;
use Medas\PdoStorage\Queries\Query;

class Executor
{
    public function execute(\PDO $pdo, Query $query): void
    {
        $this->serializeArguments($query);

        try {
            $statement = $pdo->prepare($query->query);
            $statement->execute($query->serializedArguments);

            $query->setStatement(new Statement($statement));
        }
        catch (\Exception|\Error $e) {
            throw new PdoDatabase($e->getMessage(), $query);
        }

        if ($onComplete = $query->onComplete()) {
            $onComplete($query->storage());
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
}
