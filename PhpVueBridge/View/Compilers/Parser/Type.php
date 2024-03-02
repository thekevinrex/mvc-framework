<?php

namespace PhpVueBridge\View\Compilers\Parser;

abstract class Type
{

    /**
     * The types for the expresions
     */
    const TYPE_FUNCTION = 'function_name';

    const TYPE_START_FUNCTION = 'open_function';

    const TYPE_END_FUNCTION = 'close_function';

    const TYPE_VAR = 'variable';

    const TYPE_STRING = 'string';

    const TYPE_NUMBER = 'number';

    const TYPE_ARRAY = 'array';

    const TYPE_OBJECT = 'object';

    const TYPE_OPERATOR = 'operator';
}
