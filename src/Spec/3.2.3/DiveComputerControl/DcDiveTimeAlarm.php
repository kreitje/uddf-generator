<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveComputerControl;

use Kreitje\UddfGenerator\XmlSerializable;

final class DcDiveTimeAlarm implements XmlSerializable
{
    public function __construct(
        public readonly float $timeSpan,
        public readonly DcAlarm $dcAlarm,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('setdcdivetimealarm');
        $el->appendChild($doc->createElement('timespan', (string) $this->timeSpan));
        $el->appendChild($this->dcAlarm->toXml($doc));

        return $el;
    }
}
