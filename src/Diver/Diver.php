<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\XmlSerializable;

final class Diver implements XmlSerializable
{
    public function __construct(
        public readonly Owner $owner,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('diver');
        $el->appendChild($this->owner->toXml($doc));

        return $el;
    }
}
