<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveSite;

use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\XmlSerializable;

final class Fauna implements XmlSerializable
{
    public function __construct(
        public readonly ?Invertebrata $invertebrata = null,
        public readonly ?Vertebrata $vertebrata = null,
        public readonly ?Notes $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('fauna');

        if ($this->invertebrata !== null) {
            $el->appendChild($this->invertebrata->toXml($doc));
        }

        if ($this->vertebrata !== null) {
            $el->appendChild($this->vertebrata->toXml($doc));
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
