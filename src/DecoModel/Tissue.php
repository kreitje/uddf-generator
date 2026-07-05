<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DecoModel;

use Kreitje\UddfGenerator\Enum\TissueGas;
use Kreitje\UddfGenerator\XmlSerializable;

final class Tissue implements XmlSerializable
{
    public function __construct(
        public readonly TissueGas $gas,
        public readonly int $number,
        public readonly float $halfLife,
        public readonly float $a,
        public readonly float $b,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('tissue');
        $el->setAttribute('gas', $this->gas->value);
        $el->setAttribute('number', (string) $this->number);
        $el->setAttribute('halflife', (string) $this->halfLife);
        $el->setAttribute('a', (string) $this->a);
        $el->setAttribute('b', (string) $this->b);

        return $el;
    }
}
