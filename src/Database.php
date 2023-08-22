<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\ConfigValue;
use Medas\PdoStorage\ConfigOptions\{PdoDns, PdoName, PdoPassword, PdoUsername};
use Medas\StorageManager\Interfaces\Storage;

class Database implements Storage
{
    public function __construct(
        #[ConfigValue(PdoDns::class)] public readonly string      $dns,
        #[ConfigValue(PdoUsername::class)] public readonly string $username,
        #[ConfigValue(PdoPassword::class)] public readonly string $password,
        #[ConfigValue(PdoName::class)] public readonly string     $name,
    )
    {
    }

    public function name(): string
    {
        return $this->name;
    }
}
