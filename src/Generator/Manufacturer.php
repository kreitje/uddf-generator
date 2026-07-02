<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Generator;

use Kreitje\UddfGenerator\XmlSerializable;

final class Manufacturer implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('manufacturer');
        $el->setAttribute('id', $this->id);

        $el->appendChild($doc->createElement('name', $this->name));

        if ($this->address !== null) {
            $el->appendChild($doc->createElement('address', $this->address));
        }

        if ($this->phone !== null) {
            $el->appendChild($doc->createElement('phone', $this->phone));
        }

        if ($this->email !== null) {
            $el->appendChild($doc->createElement('email', $this->email));
        }

        return $el;
    }
}
