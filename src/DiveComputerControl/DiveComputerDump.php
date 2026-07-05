<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveComputerControl;

use Kreitje\UddfGenerator\XmlSerializable;

final class DiveComputerDump implements XmlSerializable
{
    public function __construct(
        public readonly string $linkRef,
        public readonly \DateTimeImmutable $datetime,
        public readonly string $dcDumpBase64,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('divecomputerdump');

        $link = $doc->createElement('link');
        $link->setAttribute('ref', $this->linkRef);
        $el->appendChild($link);

        $el->appendChild($doc->createElement('datetime', $this->datetime->format('Y-m-d\TH:i:s')));
        $el->appendChild($doc->createElement('dcdump', $this->dcDumpBase64));

        return $el;
    }
}
