<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Maker;

use Kreitje\UddfGenerator\Generator\Manufacturer;
use Kreitje\UddfGenerator\XmlSerializable;

final class Maker implements XmlSerializable
{
    public function __construct(
        /** @var Manufacturer[] */
        public readonly array $manufacturers = [],
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('maker');

        foreach ($this->manufacturers as $manufacturer) {
            $el->appendChild($manufacturer->toXml($doc));
        }

        return $el;
    }
}
