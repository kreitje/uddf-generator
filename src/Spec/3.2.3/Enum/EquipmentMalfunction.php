<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum EquipmentMalfunction: string
{
    case None = 'none';
    case FaceMask = 'face-mask';
    case Fins = 'fins';
    case WeightBelt = 'weight-belt';
    case BuoyancyControlDevice = 'buoyancy-control-device';
    case ThermalProtection = 'thermal-protection';
    case DiveComputer = 'dive-computer';
    case DepthGauge = 'depth-gauge';
    case PressureGauge = 'pressure-gauge';
    case BreathingApparatus = 'breathing-apparatus';
    case DecoReel = 'deco-reel';
    case Other = 'other';
}
