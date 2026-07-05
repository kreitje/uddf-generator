<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum LifeStage: string
{
    case Larva = 'larva';
    case Juvenile = 'juvenile';
    case Adult = 'adult';
}
