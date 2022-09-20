<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Structure;

use Medas\ServiceManager\Attributes\Service;

#[Service]
class ChangeFinder
{
    public function find(Blueprint $expected, Blueprint $existing): Changes|null
    {
        $foundChanges = false;
        $changes = new Changes($expected->name);

        foreach ($expected->fields as $field) {
            if ($current = $existing->field($field->name)) {
                if ($current->definition === $field->definition) {
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
