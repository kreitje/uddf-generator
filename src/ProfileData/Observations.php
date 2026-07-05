<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\DiveSite\Fauna;
use Kreitje\UddfGenerator\DiveSite\Flora;
use Kreitje\UddfGenerator\XmlSerializable;

final class Observations implements XmlSerializable
{
    public function __construct(
        public readonly ?Fauna $fauna = null,
        public readonly ?Flora $flora = null,
        public readonly ?Notes $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('observations');

        if ($this->fauna !== null) {
            $el->appendChild($this->fauna->toXml($doc));
        }

        if ($this->flora !== null) {
            $el->appendChild($this->flora->toXml($doc));
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
