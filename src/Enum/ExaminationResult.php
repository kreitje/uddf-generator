<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Enum;

enum ExaminationResult: string
{
    case Passed = 'passed';
    case Failed = 'failed';
}
