<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum Platform: string
{
    case BeachShore = 'beach-shore';
    case Pier = 'pier';
    case SmallBoat = 'small-boat';
    case CharterBoat = 'charter-boat';
    case LiveAboard = 'live-aboard';
    case Barge = 'barge';
    case Landside = 'landside';
    case HyperbaricFacility = 'hyperbaric-facility';
    case Other = 'other';
}
