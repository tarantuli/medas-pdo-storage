<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Interfaces;

use Medas\PdoStorage\Queries\QuerySet;
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Migrations\MigrationBuilder;
use Medas\StorageManager\Structure\Blueprint;

interface PdoMigrationBuilder extends MigrationBuilder
{
    public function buildQueries(Storage $storage, Blueprint $expectedStructure): QuerySet|null;
}
