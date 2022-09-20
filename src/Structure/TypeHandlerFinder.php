<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure;

use Medas\EntityManager\Types\{Binary, Boolean, DateTime, Integer, Relation, Text, Type};
use Medas\PdoStorage\Exceptions\UnhandledTypeException;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Entities\TypeSerializerFinder;

#[Service]
class TypeHandlerFinder implements TypeSerializerFinder
{
    public function __construct(
        private readonly TypeHandlers\BinaryHandler   $binaryHandler,
        private readonly TypeHandlers\BooleanHandler  $booleanHandler,
        private readonly TypeHandlers\DateTimeHandler $dateTimeHandler,
        private readonly TypeHandlers\IntegerHandler  $integerHandler,
        private readonly TypeHandlers\RelationHandler $relationHandler,
        private readonly TypeHandlers\TextHandler     $textHandler,
    )
    {
    }

    public function for(Type $type): TypeHandlers\TypeHandler
    {
        return match (true) {
            $type instanceof Text => $this->textHandler,
            $type instanceof Binary => $this->binaryHandler,
            $type instanceof DateTime => $this->dateTimeHandler,
            $type instanceof Relation => $this->relationHandler,
            $type instanceof Integer => $this->integerHandler,
            $type instanceof Boolean => $this->booleanHandler,
            default => throw new UnhandledTypeException($type),
        };
    }
}
