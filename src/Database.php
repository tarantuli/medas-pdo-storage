<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\ConfigValue;
use Medas\StorageManager\Interfaces\Storage;

readonly class Database implements Storage
{
    public function __construct(
        #[ConfigValue(ConfigOptions\PdoDns::class)]
        public string $dns,
        #[ConfigValue(ConfigOptions\PdoUsername::class)]
        public string $username,
        #[ConfigValue(ConfigOptions\PdoPassword::class)]
        public string $password,
        #[ConfigValue(ConfigOptions\PdoName::class)]
        public string $name,
    )
    {
    }

    public function name(): string
    {
        return $this->name;
    }
}
