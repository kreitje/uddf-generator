<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\XmlSerializable;

final class Waypoint implements XmlSerializable
{
    public function __construct(
        public readonly float $depth,
        public readonly float $diveTime,
        public readonly ?float $temperature = null,
        public readonly ?float $tankPressure = null,
        public readonly ?string $switchMixRef = null,
    ) {
        if ($this->depth < 0.0) {
            throw new \InvalidArgumentException('Depth cannot be negative.');
        }

        if ($this->diveTime < 0.0) {
            throw new \InvalidArgumentException('Dive time cannot be negative.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('waypoint');

        $el->appendChild($doc->createElement('depth', (string) $this->depth));
        $el->appendChild($doc->createElement('divetime', (string) $this->diveTime));

        if ($this->switchMixRef !== null) {
            $switchMix = $doc->createElement('switchmix');
            $switchMix->setAttribute('ref', $this->switchMixRef);
            $el->appendChild($switchMix);
        }

        if ($this->tankPressure !== null) {
            $el->appendChild($doc->createElement('tankpressure', (string) $this->tankPressure));
        }

        if ($this->temperature !== null) {
            $el->appendChild($doc->createElement('temperature', (string) $this->temperature));
        }

        return $el;
    }
}
