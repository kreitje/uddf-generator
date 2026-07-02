<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Gas;

use Kreitje\UddfGenerator\XmlSerializable;

final class GasDefinitions implements XmlSerializable
{
    public function __construct(
        /** @var Mix[] */
        public readonly array $mixes,
    ) {
        if ($this->mixes === []) {
            throw new \InvalidArgumentException('GasDefinitions must contain at least one mix.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('gasdefinitions');

        foreach ($this->mixes as $mix) {
            $el->appendChild($mix->toXml($doc));
        }

        return $el;
    }
}
