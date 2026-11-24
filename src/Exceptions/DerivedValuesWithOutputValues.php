<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

use Medas\Core\Exceptions\BaseException;

// Output values replace the selected columns, while derived values are selected beside
// the entity's own: the two cannot be combined in one query.
class DerivedValuesWithOutputValues extends BaseException
{
    public function __construct(string $entity)
    {
        parent::__construct($entity);
    }

    public function pattern(): string
    {
        return 'a selector of %s cannot have both output values and derived values';
    }
}
