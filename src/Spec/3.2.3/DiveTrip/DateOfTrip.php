<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveTrip;

use Kreitje\UddfGenerator\XmlSerializable;

final class DateOfTrip implements XmlSerializable
{
    public function __construct(
        public readonly \DateTimeImmutable $startDate,
        public readonly \DateTimeImmutable $endDate,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('dateoftrip');
        $el->setAttribute('startdate', $this->startDate->format('Y-m-d\TH:i:s'));
        $el->setAttribute('enddate', $this->endDate->format('Y-m-d\TH:i:s'));

        return $el;
    }
}
