<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum DivePlan: string
{
    case None = 'none';
    case Table = 'table';
    case DiveComputer = 'dive-computer';
    case AnotherDiver = 'another-diver';
}
