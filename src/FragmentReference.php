<?php

namespace Cis\GqlBuilder;

use Override;

class FragmentReference extends InlineFragment
{
    public function __toString(): string
    {
        return sprintf(
            '...%s',
            $this->name
        );
    }

    #[Override]
    public function setSelectionSet(array $selectionSet = []): Query
    {
        throw new \RuntimeException('SelectionSet is not allowed on a FragmentReference');
    }
}