<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\ConfigValue;
use Medas\PdoStorage\ConfigOptions\{PdoDns, PdoName, PdoPassword, PdoUsername};
use Medas\StorageManager\Interfaces\Storage;

readonly class Database implements Storage
{
    public function __construct(
        #[ConfigValue(PdoDns::class)]
        public string $dns,

        #[ConfigValue(PdoUsername::class)]
        public string $username,

        #[ConfigValue(PdoPassword::class)]
        public string $password,

        #[ConfigValue(PdoName::class)]
        public string $name,
    )
    {
    }

    public function name(): string
    {
        return $this->name;
    }
}
