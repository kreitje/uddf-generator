<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum Problems: string
{
    case None = 'none';
    case Equalisation = 'equalisation';
    case Vertigo = 'vertigo';
    case OutOfAir = 'out-of-air';
    case Buoyancy = 'buoyancy';
    case SharedAir = 'shared-air';
    case RapidAscent = 'rapid-ascent';
    case SeaSickness = 'sea-sickness';
    case Other = 'other';
}
