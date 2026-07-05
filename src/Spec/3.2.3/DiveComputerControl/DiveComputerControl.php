<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveComputerControl;

use Kreitje\UddfGenerator\XmlSerializable;

final class DiveComputerControl implements XmlSerializable
{
    public function __construct(
        public readonly ?SetDcData $setDcData = null,
        public readonly ?GetDcData $getDcData = null,
        /** @var DiveComputerDump[] */
        public readonly array $diveComputerDumps = [],
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('divecomputercontrol');

        if ($this->setDcData !== null) {
            $el->appendChild($this->setDcData->toXml($doc));
        }

        if ($this->getDcData !== null) {
            $el->appendChild($this->getDcData->toXml($doc));
        }

        foreach ($this->diveComputerDumps as $dump) {
            $el->appendChild($dump->toXml($doc));
        }

        return $el;
    }
}
