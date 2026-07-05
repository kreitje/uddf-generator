<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum AbundanceQuality: string
{
    case Exact = 'exact';
    case Estimated = 'estimated';
}
