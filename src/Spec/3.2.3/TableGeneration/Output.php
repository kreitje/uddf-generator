<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\TableGeneration;

use Kreitje\UddfGenerator\XmlSerializable;

final class Output implements XmlSerializable
{
    public function __construct(
        public readonly ?string $lingo = null,
        public readonly ?string $fileFormat = null,
        public readonly ?string $filename = null,
        public readonly ?string $headline = null,
        public readonly ?string $remark = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('output');

        if ($this->lingo !== null) {
            $el->appendChild($doc->createElement('lingo', $this->lingo));
        }

        if ($this->fileFormat !== null) {
            $el->appendChild($doc->createElement('fileformat', $this->fileFormat));
        }

        if ($this->filename !== null) {
            $el->appendChild($doc->createElement('filename', $this->filename));
        }

        if ($this->headline !== null) {
            $el->appendChild($doc->createElement('headline', $this->headline));
        }

        if ($this->remark !== null) {
            $el->appendChild($doc->createElement('remark', $this->remark));
        }

        return $el;
    }
}
