<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum GlobalAlarm: string
{
    case AscentWarningTooLong = 'ascent-warning-too-long';
    case SosMode = 'sos-mode';
    case WorkTooHard = 'work-too-hard';
}
