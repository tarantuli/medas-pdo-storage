<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Queries\Builders;

use Medas\Core\Attributes\Service;
use Medas\PdoStorage\Queries\{ParameterizedQuery, Query};

#[Service]
readonly class ParameterizedQueryToQuery
{
    public function compile(ParameterizedQuery $paraQuery, array $arguments = []): Query
    {
        $queryArguments = [];
        $query = $paraQuery->query;

        foreach ($paraQuery->parameters as $parameter) {
            if (array_key_exists($parameter->name, $arguments)) {
                $value = $arguments[$parameter->name];
            }
            elseif ($parameter->hasDefault) {
                $value = $parameter->default;
            }
            else {
                throw new \Exception('no value given for parameter ' . $parameter->name);
            }

            if (array_key_exists($parameter->name, $paraQuery->variableSizedParameters)) {
                $counter = 0;
                $replacements = [];

                foreach ($value as $aValue) {
                    $name = $parameter->name . '__' . ($counter++);
                    $queryArguments[$name] = $aValue;
                    $replacements[] = ':' . $name;
                }

                $query = str_replace(':' . $parameter->name, implode(',', $replacements), $query);
            }
            else {
                $queryArguments[$parameter->name] = $value;
            }
        }

        return new Query($query, $paraQuery->constants + $queryArguments, $paraQuery->database);
    }
}
