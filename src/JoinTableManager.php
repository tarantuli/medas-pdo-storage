<?php

declare(strict_types=1);

namespace Medas\PdoStorage;

use Medas\Core\Attributes\Service;
use Medas\StorageManager\Structure\Blueprint;

#[Service]
class JoinTableManager
{
    public function __construct(
        private readonly PdoStorageController $pdoStorageController,
    )
    {
    }

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

        $idForeignKey = new Blueprint\ForeignKey(
            'id',
            $sourceBlueprint->name(),
            $sourceBlueprint->primaryIndex()->fields()[0]->name,
            true
        );

        $valueField = clone $field->collectionField;
        $valueField->name = 'value';
        $valueField->isGenerated = false;

        $valueForeignKey = new Blueprint\ForeignKey(
            'value',
            $field->collectionStore,
            $field->collectionField->name,
            true,
        );

        $primaryIndex = new Blueprint\Index(
            [$idField, $valueField],
            true
        );

        $joinBlueprint->setName($this->determineName($sourceBlueprint->name(), $field->name))
            ->addField($idField)
            ->addField($valueField)
            ->addIndex($primaryIndex)
            ->addForeignKey($idForeignKey)
            ->addForeignKey($valueForeignKey);

        return $this->pdoStorageController->migrationBuilder($database)->buildQueries($database, $joinBlueprint);
    }
}
