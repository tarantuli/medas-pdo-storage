<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure;

use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Structure\Blueprint;

#[Service]
class ChangeFinder
{
    public function find(Blueprint $expected, Blueprint $existing): Changes|null
    {
        $foundChanges = false;
        $changes = new Changes($expected->name);

        foreach ($expected->fields as $field) {
            if ($current = $existing->fieldByName($field->name)) {
                /**
                 * We want to compare by class and all property values, so "==" is by design
                 *
                 * @noinspection PhpNonStrictObjectEqualityInspection
                 */
                if ($current == $field) {
                    continue;
                }

                $changes->changeFields[] = $field;
            }
            else {
                $changes->addFields[] = $field;
            }

            $foundChanges = true;
        }

        return $foundChanges ? $changes : null;
    }
}
