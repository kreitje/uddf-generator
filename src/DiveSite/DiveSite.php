<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveSite;

use Kreitje\UddfGenerator\XmlSerializable;

final class DiveSite implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?Geography $geography = null,
        public readonly ?string $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('site');
        $el->setAttribute('id', $this->id);

        $el->appendChild($doc->createElement('name', $this->name));

        if ($this->geography !== null) {
            $el->appendChild($this->geography->toXml($doc));
        }

        if ($this->notes !== null) {
            $el->appendChild($doc->createElement('notes', $this->notes));
        }

        return $el;
    }
}
