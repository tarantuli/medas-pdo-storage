<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure\TypeHandlers;

use Medas\EntityManager\Attributes\HasId;
use Medas\EntityManager\Exceptions\ValueDoesNotImplementHasIdException;
use Medas\EntityManager\MetaData\Property;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Types\Relation;
use Medas\PdoStorage\Structure\{Blueprint\ForeignKey, TypeHandlerFinder};
use Medas\ServiceManager\Attributes\Service;

#[Service]
class RelationHandler extends BaseHandler
{
    public function __construct(
        private readonly MetaDataManager $metaDataManager,
    )
    {
    }

    public function fieldDefinition(Property $property): string
    {
        // We can't inject it in the constructor due to circular dependencies
        $typeHandlerFinder = service(TypeHandlerFinder::class);
        $idProperty = $this->getIdProperty($property);

        return $typeHandlerFinder->for($idProperty->type)->fieldDefinition($idProperty);
    }

    private function getIdProperty(Property $property): ?Property
    {
        /** @var Relation $type */
        $type = $property->type;
        $metaData = $this->metaDataManager->get($type->entity);

        return $metaData->idProperty;
    }

    public function foreignKey(Property $property): ForeignKey|null
    {
        /** @var Relation $type */
        $type = $property->type;
        $metaData = $this->metaDataManager->get($type->entity);

        $idProperty = $this->getIdProperty($property);

        return new ForeignKey($property->name, $metaData->entity->store, $idProperty->name);
    }

    public function serialize(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof HasId) {
            throw new ValueDoesNotImplementHasIdException($value);
        }

        return $value->id();
    }
}
