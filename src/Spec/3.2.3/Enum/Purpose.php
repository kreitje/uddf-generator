<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum Purpose: string
{
    case Sightseeing = 'sightseeing';
    case Learning = 'learning';
    case Teaching = 'teaching';
    case Research = 'research';
    case PhotographyVideography = 'photography-videography';
    case Spearfishing = 'spearfishing';
    case Proficiency = 'proficiency';
    case Work = 'work';
    case Other = 'other';
}
