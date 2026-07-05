<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum DecoStopKind: string
{
    case Safety = 'safety';
    case Mandatory = 'mandatory';
}
