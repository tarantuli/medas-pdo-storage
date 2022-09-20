<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure;

class Blueprint
{
    public string $name;

    /** @var Blueprint\Field[] */
    public array $fields = [];

    /** @var Blueprint\Index[] */
    public array $indexes = [];

    /** @var Blueprint\ForeignKey[] */
    public array $foreignKeys = [];

    public function addField(Blueprint\Field $field): void
    {
        $this->fields[$field->name] = $field;
    }

    public function addIndex(Blueprint\Index $index): void
    {
        $this->indexes[$index->name] = $index;
    }

    public function addForeignKey(Blueprint\ForeignKey $foreignKey): void
    {
        // Store the foreign key with a unique name
        $name = $this->getForeignKeyName($foreignKey);
        $this->foreignKeys[$name] = $foreignKey;

        // Add an index on the field
        $index = new Blueprint\Index($foreignKey->field);
        $index->fields[] = $this->field($foreignKey->field);
        $this->addIndex($index);
    }

    private function getForeignKeyName(Blueprint\ForeignKey $foreignKey): string
    {
        $keyHash = sha1(json_encode([$this->name, $foreignKey->field, $foreignKey->foreignEntity, $foreignKey->foreignField]));

        return sprintf('mfk_%s', $keyHash);
    }

    public function field(string $name): Blueprint\Field|null
    {
        return isset($this->fields([$name])[0]) ? $this->fields([$name])[0] : null;
    }

    public function fields(array $names): array
    {
        return array_values(array_filter($this->fields, fn($field) => in_array($field->name, $names, true)));
    }
}
