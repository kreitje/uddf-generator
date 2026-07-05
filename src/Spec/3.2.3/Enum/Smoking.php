<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum Smoking: string
{
    case None = '0';
    case Low = '0-3';
    case Moderate = '4-10';
    case High = '11-20';
    case VeryHigh = '21-40';
    case Heavy = '40+';
}
