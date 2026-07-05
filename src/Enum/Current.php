<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum Current: string
{
    case NoCurrent = 'no-current';
    case VeryMildCurrent = 'very-mild-current';
    case MildCurrent = 'mild-current';
    case ModerateCurrent = 'moderate-current';
    case HardCurrent = 'hard-current';
    case VeryHardCurrent = 'very-hard-current';
}
