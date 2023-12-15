<?php

declare(strict_types=1);

namespace Medas\PdoStorage\JoinTables;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\PdoStorage\ConfigOptions\JoinTables\TableNamingStrategy;
use Medas\PdoStorage\Database;
use Medas\PdoStorage\PdoStorageController;
use Medas\StorageManager\Structure\Blueprint;

#[Service]
readonly class JoinTableManager
{
    public function __construct(
        private PdoStorageController $pdoStorageController,

        #[ConfigValue(TableNamingStrategy::class)]
        private NamingStrategy       $namingStrategy,
    )
    {
    }

    public function createQueries(
        Database        $database,
        Blueprint       $sourceBlueprint,
        Blueprint\Field $field
    ): iterable|null
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
            $sourceBlueprint->name,
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

        $primaryIndex = new Blueprint\Index([$idField, $valueField], true);

        $joinBlueprint->name = $this->namingStrategy->determine(
            $sourceBlueprint->name,
            $field->name
        );

        $joinBlueprint->storeOriginalClass = false;

        $joinBlueprint
            ->addField($idField)
            ->addField($valueField)
            ->addIndex($primaryIndex)
            ->addForeignKey($idForeignKey)
            ->addForeignKey($valueForeignKey);

        return $this->pdoStorageController->migrationBuilder()->buildActions(
            $database,
            $joinBlueprint
        );
    }
}
