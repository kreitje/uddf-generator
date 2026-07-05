<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DecoModel;

use Kreitje\UddfGenerator\XmlSerializable;

final class Rgbm implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        /** @var Tissue[] */
        public readonly array $tissues,
    ) {
        if ($this->tissues === []) {
            throw new \InvalidArgumentException('Rgbm requires at least one tissue.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('rgbm');
        $el->setAttribute('id', $this->id);

        foreach ($this->tissues as $tissue) {
            $el->appendChild($tissue->toXml($doc));
        }

        return $el;
    }
}
