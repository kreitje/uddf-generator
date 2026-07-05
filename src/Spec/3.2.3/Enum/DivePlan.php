<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum DivePlan: string
{
    case None = 'none';
    case Table = 'table';
    case DiveComputer = 'dive-computer';
    case AnotherDiver = 'another-diver';
}
