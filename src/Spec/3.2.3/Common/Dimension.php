<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Common;

use Kreitje\UddfGenerator\XmlSerializable;

final class Dimension implements XmlSerializable
{
    public function __construct(
        public readonly ?float $length = null,
        public readonly ?float $beam = null,
        public readonly ?float $draught = null,
        public readonly ?float $displacement = null,
        public readonly ?float $tonnage = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('shipdimension');

        if ($this->length !== null) {
            $el->appendChild($doc->createElement('length', (string) $this->length));
        }

        if ($this->beam !== null) {
            $el->appendChild($doc->createElement('beam', (string) $this->beam));
        }

        if ($this->draught !== null) {
            $el->appendChild($doc->createElement('draught', (string) $this->draught));
        }

        if ($this->displacement !== null) {
            $el->appendChild($doc->createElement('displacement', (string) $this->displacement));
        }

        if ($this->tonnage !== null) {
            $el->appendChild($doc->createElement('tonnage', (string) $this->tonnage));
        }

        return $el;
    }
}
