<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveSite;

use Kreitje\UddfGenerator\Common\Address;
use Kreitje\UddfGenerator\XmlSerializable;

final class Geography implements XmlSerializable
{
    public function __construct(
        public readonly string $location,
        public readonly ?Address $address = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?float $altitude = null,
        public readonly ?float $timezone = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('geography');

        $el->appendChild($doc->createElement('location', $this->location));

        if ($this->address !== null) {
            $el->appendChild($this->address->toXml($doc));
        }

        if ($this->latitude !== null) {
            $el->appendChild($doc->createElement('latitude', (string) $this->latitude));
        }

        if ($this->longitude !== null) {
            $el->appendChild($doc->createElement('longitude', (string) $this->longitude));
        }

        if ($this->altitude !== null) {
            $el->appendChild($doc->createElement('altitude', (string) $this->altitude));
        }

        if ($this->timezone !== null) {
            $el->appendChild($doc->createElement('timezone', (string) $this->timezone));
        }

        return $el;
    }
}
