<?php

namespace Cis\GqlBuilder\Parts;

use Cis\GqlBuilder\InlineFragment;
use Cis\GqlBuilder\Query;
use InvalidArgumentException;

class SelectionSet
{
    protected array $selections = [];

    public function __construct(array $selections = [])
    {
        foreach ($selections as $selection) {
            if (is_string($selection) || $selection instanceof InlineFragment) {
                $this->selections[] = $selection;
                continue;
            }

            if ($selection instanceof Query) {
                $selection->setNested();
                $this->selections[] = $selection;
                continue;
            }

            throw new InvalidArgumentException('Provided Value in Selection set must be a string or instance of InlineFragment or Query.');
        }
    }

    public function __toString(): string
    {
        return implode(' ', $this->selections);
    }

    public function count(): int
    {
        return count($this->selections);
    }

    public function hasFields(): bool
    {
        return count(array_filter($this->selections, fn ($s) => !($s instanceof Query))) > 0;
    }
}