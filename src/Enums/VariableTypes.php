<?php

namespace Cis\GqlBuilder\Enums;

enum VariableTypes: string
{
    case String = 'String';
    case Boolean = 'Boolean';
    case Int = 'Int';
    case ID = 'ID';
    case Float = 'Float';
    case IDArray = '[ID]';
    case StringArray = '[String]';
}
