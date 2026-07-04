<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\XmlSerializable;

final class Tank implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?float $volume = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('tank');
        $el->setAttribute('id', $this->id);

        $el->appendChild($doc->createElement('name', $this->name));

        if ($this->volume !== null) {
            $el->appendChild($doc->createElement('tankvolume', (string) $this->volume));
        }

        return $el;
    }
}
