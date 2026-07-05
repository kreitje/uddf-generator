<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum Sex: string
{
    case Undetermined = 'undetermined';
    case Male = 'male';
    case Female = 'female';
    case Hermaphrodite = 'hermaphrodite';
}
