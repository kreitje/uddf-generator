<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\XmlSerializable;

final class GradientFactor implements XmlSerializable
{
    public function __construct(
        public readonly float $value,
        public readonly ?int $tissue = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('gradientfactor', (string) $this->value);

        if ($this->tissue !== null) {
            $el->setAttribute('tissue', (string) $this->tissue);
        }

        return $el;
    }
}
