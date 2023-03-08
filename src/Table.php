<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\PdoStorage\Queries\Query;
use Medas\StorageManager\Interfaces\{Store, StoreRecord};
use Medas\StorageManager\UnitOfWork\Action;

class Table implements Store
{
    private DatabaseController $controller;

    public function __construct(
        public Database $database,
        public string   $name,
    )
    {
        $this->controller = $this->database->controller();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function storage(): Database
    {
        return $this->database;
    }

    public function fetchAll(array $filters): array|null
    {
        $query = $this->prepareGet($filters);
        $query->execute();

        return $query->recordSet()->fetchRecords();
    }

    public function prepareGet(array $filters): Action
    {
        return $this->controller->actionBuilder()->select([$this], $filters);
    }

    public function prepareCreate(array $values): Action
    {
        return $this->controller->actionBuilder()->create($this, $values);
    }

    public function prepareUpdate(array $updates, array $conditions): Action
    {
        return $this->controller->actionBuilder()->update($this, $updates, $conditions);
    }

    public function prepareDelete(array $conditions): Action
    {
        return $this->controller->actionBuilder()->delete($this, $conditions);
    }

    public function getCreateTable(): string|null
    {
        try {
            $query = $this->controller->actionBuilder()->showCreate($this);
            $query->execute();
            $record = $query->recordSet()->fetchRecord();

            if ($record instanceof Record) {
                $data = $record->data();

                if (array_key_exists('Create Table', $data)) {
                    return $data['Create Table'];
                }

                if (array_key_exists('sql', $data)) {
                    return $data['sql'];
                }
            }

            return null;
        }
        catch (\Exception) {
            return null;
        }
    }

    public function fetchRecord(array $filters): StoreRecord|null
    {
        $query = $this->prepareGet($filters);
        $query->execute();

        return $query->recordSet()->fetchRecord();
    }

    public function exists(): bool
    {
        /** @var Query $query */
        $query = $this->controller->actionBuilder()->showTables($this->name);
        $query->execute();

        return $query->recordSet()->fetchRecord() !== null;
    }
}
