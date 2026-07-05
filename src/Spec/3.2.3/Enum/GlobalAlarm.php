<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum GlobalAlarm: string
{
    case AscentWarningTooLong = 'ascent-warning-too-long';
    case SosMode = 'sos-mode';
    case WorkTooHard = 'work-too-hard';
}
