<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\XmlSerializable;

final class BatteryChargeCondition implements XmlSerializable
{
    public function __construct(
        public readonly float $value,
        public readonly string $deviceRef,
        public readonly ?string $tankRef = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('batterychargecondition', (string) $this->value);
        $el->setAttribute('deviceref', $this->deviceRef);

        if ($this->tankRef !== null) {
            $el->setAttribute('tankref', $this->tankRef);
        }

        return $el;
    }
}
