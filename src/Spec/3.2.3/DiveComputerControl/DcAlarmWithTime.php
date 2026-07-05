<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveComputerControl;

use Kreitje\UddfGenerator\XmlSerializable;

final class DcAlarmWithTime implements XmlSerializable
{
    public function __construct(
        public readonly \DateTimeImmutable $datetime,
        public readonly DcAlarm $dcAlarm,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('setdcalarmtime');
        $el->appendChild($doc->createElement('datetime', $this->datetime->format('Y-m-d\TH:i:s')));
        $el->appendChild($this->dcAlarm->toXml($doc));

        return $el;
    }
}
