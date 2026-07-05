<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\ProfileData;

use Kreitje\UddfGenerator\Spec\V323\Enum\AlarmType;
use Kreitje\UddfGenerator\XmlSerializable;

final class Alarm implements XmlSerializable
{
    public function __construct(
        public readonly AlarmType $type,
        public readonly ?float $level = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('alarm', $this->type->value);

        if ($this->level !== null) {
            $el->setAttribute('level', (string) $this->level);
        }

        return $el;
    }
}
