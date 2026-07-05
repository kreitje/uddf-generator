<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\Common\Price;
use Kreitje\UddfGenerator\Common\Shop;
use Kreitje\UddfGenerator\XmlSerializable;

final class Purchase implements XmlSerializable
{
    public function __construct(
        public readonly ?\DateTimeImmutable $datetime = null,
        public readonly ?Price $price = null,
        public readonly ?Shop $shop = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('purchase');

        if ($this->datetime !== null) {
            $el->appendChild($doc->createElement('datetime', $this->datetime->format('Y-m-d\TH:i:s')));
        }

        if ($this->price !== null) {
            $el->appendChild($this->price->toXml($doc, 'price'));
        }

        if ($this->shop !== null) {
            $el->appendChild($this->shop->toXml($doc));
        }

        return $el;
    }
}
