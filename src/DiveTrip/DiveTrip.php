<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveTrip;

use Kreitje\UddfGenerator\XmlSerializable;

final class DiveTrip implements XmlSerializable
{
    public function __construct(
        /** @var Trip[] */
        public readonly array $trips,
    ) {
        if ($this->trips === []) {
            throw new \InvalidArgumentException('DiveTrip requires at least one trip.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('divetrip');

        foreach ($this->trips as $trip) {
            $el->appendChild($trip->toXml($doc));
        }

        return $el;
    }
}
