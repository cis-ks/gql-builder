<?php

namespace Cis\GqlBuilder;

use Override;

class InlineFragment extends Query
{
    public function __toString(): string
    {
        return $this->selectionSet->count() == 0
            ? sprintf('...%s', $this->name)
            : sprintf(
                '... on %s{%s}',
                $this->name,
                $this->selectionSet
            );
    }

    #[Override]
    public function setArguments(array $arguments = []): Query
    {
        throw new \RuntimeException('Arguments are not allowed on InlineFragments');
    }

    #[Override]
    public function setVariables(array $variables = []): Query
    {
        throw new \RuntimeException('Arguments are not allowed on InlineFragments');
    }
}