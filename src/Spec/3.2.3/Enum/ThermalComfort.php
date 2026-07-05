<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum ThermalComfort: string
{
    case NotIndicated = 'not-indicated';
    case Comfortable = 'comfortable';
    case Cold = 'cold';
    case VeryCold = 'very-cold';
    case Hot = 'hot';
}
