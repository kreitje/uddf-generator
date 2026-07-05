<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum DecoStopKind: string
{
    case Safety = 'safety';
    case Mandatory = 'mandatory';
}
