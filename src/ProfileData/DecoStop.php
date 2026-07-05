<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\Enum\DecoStopKind;
use Kreitje\UddfGenerator\XmlSerializable;

final class DecoStop implements XmlSerializable
{
    public function __construct(
        public readonly DecoStopKind $kind,
        public readonly float $decoDepth,
        public readonly float $duration,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('decostop');
        $el->setAttribute('kind', $this->kind->value);
        $el->setAttribute('decodepth', (string) $this->decoDepth);
        $el->setAttribute('duration', (string) $this->duration);

        return $el;
    }
}
