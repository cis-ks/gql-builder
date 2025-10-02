<?php

namespace Cis\GqlBuilder\Parts;

use InvalidArgumentException;
use Override;

class RawArgument extends Argument
{
    public function __construct(
        protected string $name,
        protected string|int|float|bool|array $value,
    ) {
        if (!is_string($this->value)) {
            throw new InvalidArgumentException('RawArgument requires value to be a string');
        }

        parent::__construct($name, $value);
    }

    #[Override]
    protected function generateValue(mixed $value): string
    {
        return is_null($value) ? $this->generateNullValue() : $value;
    }
}
