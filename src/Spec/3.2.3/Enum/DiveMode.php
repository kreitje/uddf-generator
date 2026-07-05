<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum DiveMode: string
{
    case Apnea = 'apnea';
    case Apnoe = 'apnoe';
    case Closedcircuit = 'closedcircuit';
    case Opencircuit = 'opencircuit';
    case Semiclosedcircuit = 'semiclosedcircuit';
}
