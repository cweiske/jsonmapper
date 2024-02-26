<?php

declare(strict_types=1);

namespace Enums;

enum StringBackedEnum: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}
