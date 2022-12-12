<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Drivers\Bases;

use Medas\EntityManager\Types\Integer;
use Medas\PdoStorage\Drivers\Interfaces\TableStructureFinder;
use Medas\PdoStorage\Table;
use Medas\StorageManager\Structure\{Blueprint, Blueprint\Field, Blueprint\Type};

abstract class BaseTableStructureFinder implements TableStructureFinder
{
    protected Blueprint $blueprint;
    protected string|null $createTable;

    public function find(Table $table): Blueprint|null
    {
        $this->blueprint = new Blueprint();
        $this->createTable = $table->getCreateTable();

        if ($this->createTable === null) {
            return null;
        }

        $this->findName();
        $this->findFields();
        $this->findPrimaryKey();
        $this->findKeys();

        return $this->blueprint;
    }

    abstract protected function findName();

    protected function findFields(): void
    {
        if (!preg_match_all('/^ +`([^`]+)` (.+?),?$/m', $this->createTable, $matches, PREG_SET_ORDER)) {
            return;
        }

        foreach ($matches as $match) {
            $definition = $match[2];
            $isNullable = true;
            $isGenerated = false;

            // Strip collation
            $definition = preg_replace('/ COLLATE \w+/', '', $definition);

            if (str_ends_with($definition, ' AUTO_INCREMENT')) {
                $isNullable = false;
                $isGenerated = true;
                $definition = substr($definition, 0, -strlen(' AUTO_INCREMENT'));
            }

            if (preg_match('/^(.+) DEFAULT (.+)$/', $definition, $defaultMatch)) {
                $hasDefault = true;
                $default = $defaultMatch[2];
                $definition = $defaultMatch[1];
            }
            else {
                $hasDefault = false;
                $default = null;
            }

            if (str_ends_with($definition, ' NOT NULL')) {
                $isNullable = false;
                $definition = substr($definition, 0, -strlen(' NOT NULL'));
            }

            $isInt = preg_match('/((?:tiny|medium|big)?int)(?:\(\d+\))?( unsigned)?/', $definition, $intMatch);

            $type = match (true) {
                (bool) $isInt => Type::Integer,
                str_starts_with($definition, 'varchar(') || str_starts_with($definition, 'char(') => Type::Text,
                str_starts_with($definition, 'varbinary(') || str_starts_with($definition, 'binary(') => Type::Binary,
                $definition === 'datetime' => Type::DateTime,
                default => throw new \Exception('unhandled definition "' . $definition . '"'),
            };

            $minValue = 0;
            $maxValue = Integer::UNSIGNED_4_BYTE_MAX;

            $minLength = 0;
            $maxLength = Integer::UNSIGNED_1_BYTE_MAX;

            if ($isInt) {
                if (isset($intMatch[2])) {
                    $maxValue = match ($intMatch[1]) {
                        'tinyint' => Integer::UNSIGNED_1_BYTE_MAX,
                        'mediumint' => Integer::UNSIGNED_2_BYTE_MAX,
                        'int' => Integer::UNSIGNED_3_BYTE_MAX,
                        default => Integer::UNSIGNED_4_BYTE_MAX,
                    };
                }
                else {
                    $minValue = match ($intMatch[1]) {
                        'tinyint' => Integer::SIGNED_1_BYTE_MIN,
                        'mediumint' => Integer::SIGNED_2_BYTE_MIN,
                        'int' => Integer::SIGNED_3_BYTE_MIN,
                        default => Integer::SIGNED_4_BYTE_MIN,
                    };
                    $maxValue = match ($intMatch[1]) {
                        'tinyint' => Integer::SIGNED_1_BYTE_MAX,
                        'mediumint' => Integer::SIGNED_2_BYTE_MAX,
                        'int' => Integer::SIGNED_3_BYTE_MAX,
                        default => Integer::SIGNED_4_BYTE_MAX,
                    };
                }
            }

            $this->blueprint->addField(
                new Field(
                    $match[1], $type, $isNullable, $isGenerated, $hasDefault, $default,
                    $minValue, $maxValue, $minLength, $maxLength,
                )
            );
        }
    }

    abstract protected function findPrimaryKey();

    abstract protected function findKeys();
}
