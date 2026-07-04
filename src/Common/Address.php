<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Common;

use Kreitje\UddfGenerator\XmlSerializable;

final class Address implements XmlSerializable
{
    public function __construct(
        public readonly string $country,
        public readonly ?string $street = null,
        public readonly ?string $city = null,
        public readonly ?string $postcode = null,
        public readonly ?string $province = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('address');

        if ($this->street !== null) {
            $el->appendChild($doc->createElement('street', $this->street));
        }

        if ($this->city !== null) {
            $el->appendChild($doc->createElement('city', $this->city));
        }

        if ($this->postcode !== null) {
            $el->appendChild($doc->createElement('postcode', $this->postcode));
        }

        $el->appendChild($doc->createElement('country', $this->country));

        if ($this->province !== null) {
            $el->appendChild($doc->createElement('province', $this->province));
        }

        return $el;
    }
}
