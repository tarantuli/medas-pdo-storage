<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\StorageManager\Interfaces\Record as RecordInterface;

class Record implements RecordInterface
{
    private int $keyIndex;
    private array $keys;

    public function __construct(private array $data)
    {
    }

    public function data(): array
    {
        return $this->data;
    }

    public function patch(array $values): void
    {
        $this->data = array_merge($this->data, $values);
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    public function current(): mixed
    {
        return $this->data[$this->keys[$this->keyIndex]];
    }

    public function next(): void
    {
        ++$this->keyIndex;
    }

    public function key(): string
    {
        return $this->keys[$this->keyIndex];
    }

    public function valid(): bool
    {
        return array_key_exists($this->keyIndex, $this->keys);
    }

    public function rewind(): void
    {
        $this->keys = array_keys($this->data);
        $this->keyIndex = 0;
    }
}
