<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum Transportation: string
{
    case CommercialAircraft = 'commercial-aircraft';
    case UnpressurizedAircraft = 'unpressurized-aircraft';
    case MedevacAircraft = 'medevac-aircraft';
    case GroundTransportation = 'ground-transportation';
    case Helicopter = 'helicopter';
}
