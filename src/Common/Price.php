<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Common;

final class Price
{
    public function __construct(
        public readonly float $amount,
        public readonly ?string $currency = null,
    ) {
        if ($this->currency !== null && strlen($this->currency) !== 3) {
            throw new \InvalidArgumentException('Currency must be a 3-letter code.');
        }
    }

    public function toXml(\DOMDocument $doc, string $elementName): \DOMElement
    {
        $el = $doc->createElement($elementName, (string) $this->amount);

        if ($this->currency !== null) {
            $el->setAttribute('currency', $this->currency);
        }

        return $el;
    }
}
