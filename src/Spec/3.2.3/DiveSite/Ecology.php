<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveSite;

use Kreitje\UddfGenerator\XmlSerializable;

final class Ecology implements XmlSerializable
{
    public function __construct(
        public readonly ?Fauna $fauna = null,
        public readonly ?Flora $flora = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('ecology');

        if ($this->fauna !== null) {
            $el->appendChild($this->fauna->toXml($doc));
        }

        if ($this->flora !== null) {
            $el->appendChild($this->flora->toXml($doc));
        }

        return $el;
    }
}
