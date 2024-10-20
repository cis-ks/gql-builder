<?php

namespace Cis\GqlBuilder;

class Fragment extends Query
{
    protected const string OPERATION_NAME = 'fragment';

    public function __construct(
        string $name,
        protected string $reference)
    {
        parent::__construct($name);
    }

    public function __toString(): string
    {
        return sprintf(
            '%s %s on %s {%s}',
            static::OPERATION_NAME,
            $this->name,
            $this->reference,
            $this->selectionSet
        );
    }
}