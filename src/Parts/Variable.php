<?php

namespace Cis\GqlBuilder\Parts;

use Cis\GqlBuilder\Enums\VariableTypes;
use Cis\GqlBuilder\Utils\StringFormatter;

class Variable
{
    public function __construct(
        protected string $name,
        protected VariableTypes $type,
        protected bool $required = false,
        protected bool $multiple = false,
    ) {
    }

    public function __toString(): string
    {
        return StringFormatter::cleanSpaces(sprintf(
            '$%s: %s%s%s%s',
            ltrim($this->name, '$'),
            $this->multiple ? '[' : '',
            $this->type->name,
            $this->required ? '!' : '',
            $this->multiple ? ']' : '',
        ));
    }
}