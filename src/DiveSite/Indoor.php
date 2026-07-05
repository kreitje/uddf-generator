<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveSite;

use Kreitje\UddfGenerator\Common\Address;
use Kreitje\UddfGenerator\Common\Contact;
use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\XmlSerializable;

final class Indoor implements XmlSerializable
{
    public function __construct(
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?Address $address = null,
        public readonly ?Contact $contact = null,
        public readonly ?Notes $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('indoor');

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

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
