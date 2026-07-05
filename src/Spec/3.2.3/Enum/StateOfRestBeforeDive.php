<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum StateOfRestBeforeDive: string
{
    case NotSpecified = 'not-specified';
    case Rested = 'rested';
    case Tired = 'tired';
    case Exhausted = 'exhausted';
}
