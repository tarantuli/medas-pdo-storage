<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\Service;
use Medas\StorageManager\Structure\Blueprint;

#[Service]
class JoinTableManager
{
    public function determineName(string $sourceTable, string $property): string
    {
        return $sourceTable . '__' . $property;
    }

    public function createQueries(Database $database, Blueprint $sourceBlueprint, Blueprint\Field $field): iterable|null
    {
        $joinBlueprint = new Blueprint();

        if ($sourceBlueprint->primaryIndex() === null) {
            return null;
        }

        $idField = clone $sourceBlueprint->primaryIndex()->fields()[0];
        $idField->name = 'id';
        $idField->isGenerated = false;

        $valueField = clone $field->collectionField;
        $valueField->name = 'value';
        $valueField->isGenerated = false;

        $foreignKey = new Blueprint\ForeignKey(
            'value',
            $field->collectionStore,
            $field->collectionField->name,
            true,
        );

        $joinBlueprint->setName($this->determineName($sourceBlueprint->name(), $field->name))
            ->addField($idField)
            ->addField($valueField)
            ->addForeignKey($foreignKey);

        return $database->controller()->migrationBuilder()->buildQueries($joinBlueprint);
    }
}
