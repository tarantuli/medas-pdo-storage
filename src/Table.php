<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\StorageManager\Interfaces\{Store, StoreRecord};
use Medas\StorageManager\UnitOfWork\Action;

class Table implements Store
{
    public function __construct(
        public Database $database,
        public string   $name,
    )
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function storage(): Database
    {
        return $this->database;
    }

    public function fetchRecord(array $filters): StoreRecord|null
    {
        $query = $this->prepareGet($filters);
        $query->execute();

        return $query->recordSet()->fetchRecord();
    }

    public function prepareGet(array $filters): Action
    {
        return $this->database->queryBuilder()->select([$this], $filters);
    }

    public function fetchAll(array $filters): array|null
    {
        $query = $this->prepareGet($filters);
        $query->execute();

        return $query->recordSet()->fetchRecords();
    }

    public function prepareCreate(array $values): Action
    {
        return $this->database->queryBuilder()->create($this, $values);
    }

    public function prepareUpdate(array $updates, array $conditions): Action
    {
        return $this->database->queryBuilder()->update($this, $updates, $conditions);
    }

    public function prepareDelete(array $conditions): Action
    {
        return $this->database->queryBuilder()->delete($this, $conditions);
    }

    public function getCreateTable(): string|null
    {
        try {
            $query = $this->database->queryBuilder()->showCreate($this);
            $query->execute();
            return $query->recordSet()->fetchRecord()['Create Table'];
        }
        catch (\Exception) {
            return null;
        }
    }

    public function exists(): bool
    {
        $query = $this->database->queryBuilder()->showTables($this->name);
        $query->execute();

        return $query->recordSet()->fetchRecord() !== null;
    }
}
