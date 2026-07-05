<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\Enum\SetPo2SetBy;
use Kreitje\UddfGenerator\XmlSerializable;

final class SetPo2 implements XmlSerializable
{
    public function __construct(
        public readonly float $value,
        public readonly SetPo2SetBy $setBy,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('setpo2', (string) $this->value);
        $el->setAttribute('setby', $this->setBy->value);

        return $el;
    }
}
