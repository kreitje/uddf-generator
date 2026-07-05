<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\XmlSerializable;

final class WayAltitude implements XmlSerializable
{
    public function __construct(
        public readonly float $value,
        public readonly float $wayTime,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('wayaltitude', (string) $this->value);
        $el->setAttribute('waytime', (string) $this->wayTime);

        return $el;
    }
}
