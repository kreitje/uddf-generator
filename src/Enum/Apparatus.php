<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum Apparatus: string
{
    case OpenScuba = 'open-scuba';
    case Rebreather = 'rebreather';
    case SurfaceSupplied = 'surface-supplied';
    case Chamber = 'chamber';
    case Experimental = 'experimental';
}
