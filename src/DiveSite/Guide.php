<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveSite;

use Kreitje\UddfGenerator\XmlSerializable;

final class Guide implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $linkRef,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('guide');
        $el->setAttribute('id', $this->id);

        $link = $doc->createElement('link');
        $link->setAttribute('ref', $this->linkRef);
        $el->appendChild($link);

        return $el;
    }
}
