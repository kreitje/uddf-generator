<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum MeteringMethod: string
{
    case Spot = 'spot';
    case Centerweighted = 'centerweighted';
    case Matrix = 'matrix';
}
