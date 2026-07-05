<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum Program: string
{
    case Recreation = 'recreation';
    case Training = 'training';
    case Scientific = 'scientific';
    case Medical = 'medical';
    case Commercial = 'commercial';
    case Military = 'military';
    case Competitive = 'competitive';
    case Other = 'other';
}
