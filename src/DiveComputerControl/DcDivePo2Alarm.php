<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveComputerControl;

use Kreitje\UddfGenerator\XmlSerializable;

final class DcDivePo2Alarm implements XmlSerializable
{
    public function __construct(
        public readonly float $maximumPo2,
        public readonly DcAlarm $dcAlarm,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('setdcdivepo2alarm');
        $el->appendChild($doc->createElement('maximumpo2', (string) $this->maximumPo2));
        $el->appendChild($this->dcAlarm->toXml($doc));

        return $el;
    }
}
