<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure;

use Medas\EntityManager\{MetaData, MetaDataManager};
use Medas\ServiceManager\Attributes\Service;

#[Service]
class EntityStructureFinder
{
    private MetaData $metaData;
    private Blueprint $blueprint;

    public function __construct(
        private readonly MetaDataManager   $metaDataManager,
        private readonly TypeHandlerFinder $typeHandlerFinder,
    )
    {
    }

    public function find(string $className): Blueprint
    {
        $this->metaData = $this->metaDataManager->get($className);
        $this->blueprint = new Blueprint();

        $this->findName();
        $this->findFields();
        $this->findPrimaryKey();
        $this->findKeys();
        $this->findForeignKeys();

        return $this->blueprint;
    }

    private function findName(): void
    {
        $this->blueprint->name = $this->metaData->entity->store;
    }

    private function findFields(): void
    {
        foreach ($this->metaData->properties as $property) {
            [$definition, $isNullable, $isGenerated] = $this->determineDefinition($property);
            [$hasDefault, $default] = $this->determineDefault($property);
            $this->blueprint->addField(new Blueprint\Field($property->name, $definition, $isNullable, $isGenerated, $hasDefault, $default));
        }
    }

    private function determineDefinition(MetaData\Property $property): array
    {
        $handler = $this->typeHandlerFinder->for($property->type);
        $definition = $handler->fieldDefinition($property);
        $isNullable = true;
        $isGenerated = false;

        if ($property->isGeneratedValue) {
            $isNullable = false;
            $isGenerated = true;
        }
        elseif (!$property->isNullable) {
            $isNullable = false;
        }

        return [$definition, $isNullable, $isGenerated];
    }

    private function determineDefault(MetaData\Property $property): array
    {
        if ($property->isGeneratedValue) {
            return [false, null];
        }
        elseif ($property->isNullable || $property->default !== null) {
            return [true, $property->default];
        }
        else {
            return [false, null];
        }
    }

    private function findPrimaryKey(): void
    {
        $index = new Blueprint\Index('PRIMARY');

        foreach ($this->metaData->idProperties as $property) {
            $index->fields[] = $this->blueprint->field($property->name);
        }

        $index->isUnique = true;
        $this->blueprint->addIndex($index);
    }

    private function findKeys(): void
    {
        // Unique values
        foreach ($this->metaData->properties as $property) {
            if (!$property->isUnique) {
                continue;
            }

            $index = new Blueprint\Index($property->name);
            $index->fields[] = $this->blueprint->field($property->name);
            $index->isUnique = true;
            $this->blueprint->addIndex($index);
        }
    }

    private function findForeignKeys(): void
    {
        foreach ($this->metaData->properties as $property) {
            $handler = $this->typeHandlerFinder->for($property->type);

            if ($foreignKey = $handler->foreignKey($property)) {
                $this->blueprint->addForeignKey($foreignKey);
            }
        }
    }
}
