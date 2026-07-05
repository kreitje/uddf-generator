<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\TableGeneration;

use Kreitje\UddfGenerator\XmlSerializable;

final class TableScope implements XmlSerializable
{
    public function __construct(
        public readonly ?float $altitude = null,
        public readonly ?float $diveDepthBegin = null,
        public readonly ?float $diveDepthEnd = null,
        public readonly ?float $diveDepthStep = null,
        public readonly ?float $bottomTimeMaximum = null,
        public readonly ?float $bottomTimeMinimum = null,
        public readonly ?float $bottomTimeStepBegin = null,
        public readonly ?float $bottomTimeStepEnd = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('tablescope');

        if ($this->altitude !== null) {
            $el->appendChild($doc->createElement('altitude', (string) $this->altitude));
        }

        if ($this->diveDepthBegin !== null) {
            $el->appendChild($doc->createElement('divedepthbegin', (string) $this->diveDepthBegin));
        }

        if ($this->diveDepthEnd !== null) {
            $el->appendChild($doc->createElement('divedepthend', (string) $this->diveDepthEnd));
        }

        if ($this->diveDepthStep !== null) {
            $el->appendChild($doc->createElement('divedepthstep', (string) $this->diveDepthStep));
        }

        if ($this->bottomTimeMaximum !== null) {
            $el->appendChild($doc->createElement('bottomtimemaximum', (string) $this->bottomTimeMaximum));
        }

        if ($this->bottomTimeMinimum !== null) {
            $el->appendChild($doc->createElement('bottomtimeminimum', (string) $this->bottomTimeMinimum));
        }

        if ($this->bottomTimeStepBegin !== null) {
            $el->appendChild($doc->createElement('bottomtimestepbegin', (string) $this->bottomTimeStepBegin));
        }

        if ($this->bottomTimeStepEnd !== null) {
            $el->appendChild($doc->createElement('bottomtimestepend', (string) $this->bottomTimeStepEnd));
        }

        return $el;
    }
}
