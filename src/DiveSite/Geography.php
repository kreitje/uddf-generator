<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveSite;

use Kreitje\UddfGenerator\XmlSerializable;

final class Geography implements XmlSerializable
{
    public function __construct(
        public readonly ?string $location = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $country = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('geography');

        if ($this->location !== null) {
            $el->appendChild($doc->createElement('location', $this->location));
        }

        if ($this->country !== null) {
            $el->appendChild($doc->createElement('country', $this->country));
        }

        if ($this->latitude !== null) {
            $el->appendChild($doc->createElement('latitude', (string) $this->latitude));
        }

        if ($this->longitude !== null) {
            $el->appendChild($doc->createElement('longitude', (string) $this->longitude));
        }

        return $el;
    }
}
