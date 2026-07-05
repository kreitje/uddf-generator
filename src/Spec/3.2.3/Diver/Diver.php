<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Diver;

use Kreitje\UddfGenerator\XmlSerializable;

final class Diver implements XmlSerializable
{
    public function __construct(
        public readonly Owner $owner,
        /** @var Buddy[] */
        public readonly array $buddies = [],
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('diver');
        $el->appendChild($this->owner->toXml($doc));

        foreach ($this->buddies as $buddy) {
            $el->appendChild($buddy->toXml($doc));
        }

        return $el;
    }
}
