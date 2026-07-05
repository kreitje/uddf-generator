<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Business;

use Kreitje\UddfGenerator\Common\Shop;
use Kreitje\UddfGenerator\XmlSerializable;

final class Business implements XmlSerializable
{
    public function __construct(
        /** @var Shop[] */
        public readonly array $shops = [],
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('business');

        foreach ($this->shops as $shop) {
            $el->appendChild($shop->toXml($doc));
        }

        return $el;
    }
}
