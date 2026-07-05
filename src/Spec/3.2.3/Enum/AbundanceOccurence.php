<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum AbundanceOccurence: string
{
    case NotAscertainable = 'not-ascertainable';
    case SingleIndividual = 'single-individual';
    case LooseAssociation = 'loose-association';
    case Swarm = 'swarm';
    case Colony = 'colony';
}
