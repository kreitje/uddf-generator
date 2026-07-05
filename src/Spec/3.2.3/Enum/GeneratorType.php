<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum GeneratorType: string
{
    case Converter = 'converter';
    case Divecomputer = 'divecomputer';
    case Logbook = 'logbook';
}
