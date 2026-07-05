<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum SuitType: string
{
    case DiveSkin = 'dive-skin';
    case WetSuit = 'wet-suit';
    case DrySuit = 'dry-suit';
    case HotWaterSuit = 'hot-water-suit';
    case Other = 'other';
}
