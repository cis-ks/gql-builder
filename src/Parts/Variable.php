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
        protected mixed $defaultValue = null,
    ) {
        if (str_starts_with($this->type->value, '[') && str_ends_with($this->type->value, ']')) {
            $this->multiple = true;
            $this->type = VariableTypes::{str_replace('Array', '', $this->type->name)};
        }
    }

    public function __toString(): string
    {
        return StringFormatter::cleanSpaces(sprintf(
            '$%s: %s%s%s%s%s',
            ltrim($this->name, '$'),
            $this->multiple ? '[' : '',
            $this->type->value,
            $this->required ? '!' : '',
            $this->multiple ? ']' : '',
            $this->defaultValue === null ? '' : " = " . $this->defaultValueToString($this->defaultValue) . " ",
        ));
    }

    protected function defaultValueToString(mixed $value): string
    {
        return match(true) {
            is_integer($value), is_float($value) => $value,
            is_array($value) => sprintf(
                "[%s]",
                implode(',', array_map(fn ($v) => $this->defaultValueToString($v), $value))
            ),
            default => sprintf("\"%s\"", $value)
        };
    }
}
