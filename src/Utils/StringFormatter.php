<?php

namespace Cis\GqlBuilder\Utils;

class StringFormatter
{
    public static function cleanSpaces(string $input): string
    {
        return preg_replace('/ {2,}/', ' ', $input);
    }
}