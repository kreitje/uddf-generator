<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum GlobalLightIntensity: string
{
    case Undetermined = 'undetermined';
    case Sunny = 'sunny';
    case HalfShadow = 'half-shadow';
    case Shadow = 'shadow';
    case NoLight = 'no-light';
}
