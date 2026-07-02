<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\XmlSerializable;

final class Owner implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly PersonalData $personalData,
        public readonly ?Equipment $equipment = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('owner');
        $el->setAttribute('id', $this->id);

        $el->appendChild($this->personalData->toXml($doc));

        if ($this->equipment !== null) {
            $el->appendChild($this->equipment->toXml($doc));
        }

        return $el;
    }
}
