<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Generator;

use Kreitje\UddfGenerator\Spec\V323\Common\Address;
use Kreitje\UddfGenerator\Spec\V323\Common\Contact;
use Kreitje\UddfGenerator\XmlSerializable;

final class Manufacturer implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?Address $address = null,
        public readonly ?Contact $contact = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('manufacturer');
        $el->setAttribute('id', $this->id);

        $el->appendChild($doc->createElement('name', $this->name));

        foreach ($this->aliasNames as $aliasName) {
            $el->appendChild($doc->createElement('aliasname', $aliasName));
        }

        if ($this->address !== null) {
            $el->appendChild($this->address->toXml($doc));
        }

        if ($this->contact !== null) {
            $el->appendChild($this->contact->toXml($doc));
        }

        return $el;
    }
}
