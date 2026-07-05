<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveComputerControl;

use Kreitje\UddfGenerator\XmlSerializable;

final class DcAlarmWithDepth implements XmlSerializable
{
    public function __construct(
        public readonly float $dcAlarmDepth,
        public readonly DcAlarm $dcAlarm,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('setdcdivedepthalarm');
        $el->appendChild($doc->createElement('dcalarmdepth', (string) $this->dcAlarmDepth));
        $el->appendChild($this->dcAlarm->toXml($doc));

        return $el;
    }
}
