<?php

namespace Cis\GqlBuilder\Parts;

use InvalidArgumentException;

class FiltersArgument extends Argument
{
    public function __construct(
        protected string|int|float|bool|array $value,
    ) {
        parent::__construct('filters', $value, true);

        if ($this->isQueryType && !is_array($value)) {
            throw new InvalidArgumentException('Argument as Object requires value to be an array');
        }
    }
}
