<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Enum;

enum ExaminationResult: string
{
    case Passed = 'passed';
    case Failed = 'failed';
}
