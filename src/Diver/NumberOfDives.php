<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\XmlSerializable;

final class NumberOfDives implements XmlSerializable
{
    public function __construct(
        public readonly \DateTimeImmutable $startDate,
        public readonly \DateTimeImmutable $endDate,
        public readonly int $dives,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('numberofdives');
        $el->setAttribute('startdate', $this->startDate->format('Y-m-d\TH:i:s'));
        $el->setAttribute('enddate', $this->endDate->format('Y-m-d\TH:i:s'));
        $el->setAttribute('dives', (string) $this->dives);

        return $el;
    }
}
