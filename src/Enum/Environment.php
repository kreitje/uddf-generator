<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum Environment: string
{
    case Unknown = 'unknown';
    case OceanSea = 'ocean-sea';
    case LakeQuarry = 'lake-quarry';
    case RiverSpring = 'river-spring';
    case CaveCavern = 'cave-cavern';
    case Pool = 'pool';
    case HyperbaricChamber = 'hyperbaric-chamber';
    case UnderIce = 'under-ice';
    case Other = 'other';
}
