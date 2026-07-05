<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum DiveTable: string
{
    case Padi = 'PADI';
    case Naui = 'NAUI';
    case Bsac = 'BSAC';
    case Buehlmann = 'Buehlmann';
    case Dciem = 'DCIEM';
    case UsNavy = 'US-Navy';
    case Csmd = 'CSMD';
    case Comex = 'COMEX';
    case Other = 'other';
}
