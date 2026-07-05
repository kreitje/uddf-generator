<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Common;

use Kreitje\UddfGenerator\XmlSerializable;

final class PricePerDivePackage implements XmlSerializable
{
    public function __construct(
        public readonly float $amount,
        public readonly ?string $currency = null,
        public readonly ?string $noOfDives = null,
    ) {
        if ($this->currency !== null && strlen($this->currency) !== 3) {
            throw new \InvalidArgumentException('Currency must be a 3-letter code.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('pricedivepackage', (string) $this->amount);

        if ($this->currency !== null) {
            $el->setAttribute('currency', $this->currency);
        }

        if ($this->noOfDives !== null) {
            $el->setAttribute('noofdives', $this->noOfDives);
        }

        return $el;
    }
}
