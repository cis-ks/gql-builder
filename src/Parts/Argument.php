<?php

namespace Cis\GqlBuilder\Parts;

use InvalidArgumentException;

class Argument
{
    public function __construct(
        protected string                      $name,
        protected string|int|float|bool|array $value,
        protected bool                        $isQueryType = false,
    ) {
        if ($this->isQueryType && !is_array($value)) {
            throw new InvalidArgumentException('Argument as Object requires value to be an array');
        }
    }

    public function __toString(): string
    {
        return sprintf(
            '%s: %s',
            $this->name,
            $this->generateValue($this->value)
        );
    }

    protected function generateValue(mixed $value): string
    {
        return match (true) {
            is_null($value) => $this->generateNullValue(),
            is_string($value) => $this->generateStringValue($value),
            is_int($value) || is_float($value) => $this->generateIntFloatValue($value),
            is_bool($value) => $this->generateBoolValue($value),
            is_array($value) && $this->isQueryType => $this->generateObjectValue($value),
            is_array($value) => $this->generateArrayValue($value),
        };
    }

    protected function generateNullValue(): string
    {
        return 'null';
    }

    protected function generateStringValue(mixed $value): string
    {
        return str_starts_with($value, '$')
            ? $value
            : '"' . addslashes($value) . '"';
    }

    protected function generateIntFloatValue(mixed $value): string
    {
        return (string)$value;
    }

    protected function generateBoolValue(mixed $value): string
    {
        return $value ? 'true' : 'false';
    }

    protected function generateObjectValue(mixed $value): string
    {
        $value = stripslashes(json_encode($value, JSON_FORCE_OBJECT));
        $value = preg_replace('/([{,])"([^"]+)":/', '$1$2: ', $value);
        return preg_replace('/"(\$[^"]+)"/', '$1', $value);
    }

    protected function generateArrayValue(mixed $value): string
    {
        return implode(', ', array_map(fn ($v) => $this->generateValue($v), $value));
    }
}