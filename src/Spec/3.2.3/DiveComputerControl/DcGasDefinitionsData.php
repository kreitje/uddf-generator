<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveComputerControl;

use Kreitje\UddfGenerator\XmlSerializable;

final class DcGasDefinitionsData implements XmlSerializable
{
    public function __construct(
        public readonly bool $allGasDefinitions = false,
        /** @var string[] */
        public readonly array $gasDataRefs = [],
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('setdcgasdefinitionsdata');

        if ($this->allGasDefinitions) {
            $el->appendChild($doc->createElement('setdcallgasdefinitions'));
        }

        foreach ($this->gasDataRefs as $ref) {
            $el->appendChild($doc->createElement('setdcgasdata', $ref));
        }

        return $el;
    }
}
