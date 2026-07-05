<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum Workload: string
{
    case NotSpecified = 'not-specified';
    case Resting = 'resting';
    case Light = 'light';
    case Moderate = 'moderate';
    case Severe = 'severe';
    case Exhausting = 'exhausting';
}
