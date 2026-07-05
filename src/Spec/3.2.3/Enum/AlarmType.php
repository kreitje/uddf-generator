<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum AlarmType: string
{
    case Ascent = 'ascent';
    case Breath = 'breath';
    case Deco = 'deco';
    case Error = 'error';
    case Link = 'link';
    case Microbubbles = 'microbubbles';
    case Rbt = 'rbt';
    case Skincooling = 'skincooling';
    case Surface = 'surface';
}
