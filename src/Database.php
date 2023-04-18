<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\ConfigValue;
use Medas\PdoStorage\ConfigOptions\{PdoDns, PdoPassword, PdoUsername};
use Medas\StorageManager\Interfaces\{Storage, StorageController};

class Database implements Storage
{

    /** @var Table[] */
    private array $tables = [];
    private DatabaseController $controller;

    public function __construct(
        #[ConfigValue(PdoDns::class)] private readonly string      $dns,
        #[ConfigValue(PdoUsername::class)] private readonly string $username,
        #[ConfigValue(PdoPassword::class)] private readonly string $password,
    )
    {
        $this->controller = new DatabaseController($this, $this->dns, $this->username, $this->password);
    }

    public function stores(): array
    {
        return $this->tables;
    }

    public function store(string $name): Table
    {
        if (!isset($this->tables[$name])) {
            $this->tables[$name] = new Table($this, $name);
        }

        return $this->tables[$name];
    }

    public function controller(): StorageController
    {
        return $this->controller;
    }
}
