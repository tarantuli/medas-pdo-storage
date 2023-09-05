<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Migrations\MigrationBuilder;
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\ActionSet;

interface PdoMigrationBuilder extends MigrationBuilder
{
    public function buildQueries(Storage $storage, Blueprint $expectedStructure): ActionSet|null;
}
