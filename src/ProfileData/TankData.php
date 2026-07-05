<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\XmlSerializable;

final class TankData implements XmlSerializable
{
    public function __construct(
        public readonly ?string $tankRef = null,
        public readonly ?string $mixRef = null,
        public readonly ?float $tankVolume = null,
        public readonly ?float $tankPressureBegin = null,
        public readonly ?float $tankPressureEnd = null,
        public readonly ?float $breathingConsumptionVolume = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('tankdata');

        if ($this->tankRef !== null) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $this->tankRef);
            $el->appendChild($link);
        }

        if ($this->mixRef !== null) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $this->mixRef);
            $el->appendChild($link);
        }

        if ($this->tankVolume !== null) {
            $el->appendChild($doc->createElement('tankvolume', (string) $this->tankVolume));
        }

        if ($this->tankPressureBegin !== null) {
            $el->appendChild($doc->createElement('tankpressurebegin', (string) $this->tankPressureBegin));
        }

        if ($this->tankPressureEnd !== null) {
            $el->appendChild($doc->createElement('tankpressureend', (string) $this->tankPressureEnd));
        }

        if ($this->breathingConsumptionVolume !== null) {
            $el->appendChild($doc->createElement('breathingconsumptionvolume', (string) $this->breathingConsumptionVolume));
        }

        return $el;
    }
}
