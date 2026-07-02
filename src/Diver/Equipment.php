<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\XmlSerializable;

final class Equipment implements XmlSerializable
{
    public function __construct(
        /** @var Tank[] */
        public readonly array $tanks = [],
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('equipment');

        foreach ($this->tanks as $tank) {
            $el->appendChild($tank->toXml($doc));
        }

        return $el;
    }
}
