<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\XmlSerializable;

final class MeasuredPo2Reading implements XmlSerializable
{
    public function __construct(
        public readonly float $value,
        public readonly ?string $ref = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('measuredpo2', (string) $this->value);

        if ($this->ref !== null) {
            $el->setAttribute('ref', $this->ref);
        }

        return $el;
    }
}
