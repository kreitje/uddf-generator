<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Generator;

use Kreitje\UddfGenerator\Common\Address;
use Kreitje\UddfGenerator\Common\Contact;
use Kreitje\UddfGenerator\XmlSerializable;

final class Manufacturer implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?Address $address = null,
        public readonly ?Contact $contact = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('manufacturer');
        $el->setAttribute('id', $this->id);

        $el->appendChild($doc->createElement('name', $this->name));

        if ($this->address !== null) {
            $el->appendChild($this->address->toXml($doc));
        }

        if ($this->contact !== null) {
            $el->appendChild($this->contact->toXml($doc));
        }

        return $el;
    }
}
