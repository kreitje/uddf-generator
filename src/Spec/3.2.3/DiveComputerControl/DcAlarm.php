<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveComputerControl;

use Kreitje\UddfGenerator\XmlSerializable;

final class DcAlarm implements XmlSerializable
{
    public function __construct(
        public readonly int $alarmType,
        public readonly bool $acknowledge = false,
        public readonly ?float $period = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('dcalarm');

        if ($this->acknowledge) {
            $el->appendChild($doc->createElement('acknowledge'));
        }

        if ($this->period !== null) {
            $el->appendChild($doc->createElement('period', (string) $this->period));
        }

        $el->appendChild($doc->createElement('alarmType', (string) $this->alarmType));

        return $el;
    }
}
