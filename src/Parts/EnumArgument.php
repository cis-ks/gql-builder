<?php

namespace Cis\GqlBuilder\Parts;

use InvalidArgumentException;

class EnumArgument extends Argument
{
    public function __construct(
        protected string $name,
        protected string|int|float|bool|array $value,
        protected bool $isQueryType = false,
    ) {
        if ($this->isQueryType && !is_array($value)) {
            throw new InvalidArgumentException('Argument as Object requires value to be an array');
        }

        parent::__construct($name, $value, $isQueryType, true);
    }
}
