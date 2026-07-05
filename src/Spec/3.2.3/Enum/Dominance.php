<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum Dominance: string
{
    case Undetermined = 'undetermined';
    case LessThanOneTwentieth = 'less-than-1/20';
    case OneTwentiethToOneQuarter = '1/20-up-to-1/4';
    case OneQuarterToOneHalf = '1/4-up-to-1/2';
    case OneHalfToThreeQuarters = '1/2-up-to-3/4';
    case GreaterThanThreeQuarters = 'greater-than-3/4';
    case SingleIndividual = 'single-individual';
}
