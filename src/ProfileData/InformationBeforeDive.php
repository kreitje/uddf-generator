<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\XmlSerializable;

final class InformationBeforeDive implements XmlSerializable
{
    public function __construct(
        public readonly \DateTimeImmutable $datetime,
        public readonly ?int $diveNumber = null,
        public readonly ?string $diveSiteRef = null,
        public readonly ?string $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('informationbeforedive');

        if ($this->diveNumber !== null) {
            $el->appendChild($doc->createElement('divenumber', (string) $this->diveNumber));
        }

        $el->appendChild($doc->createElement('datetime', $this->datetime->format('Y-m-d\TH:i:s')));

        if ($this->diveSiteRef !== null) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $this->diveSiteRef);
            $el->appendChild($link);
        }

        if ($this->notes !== null) {
            $el->appendChild($doc->createElement('notes', $this->notes));
        }

        return $el;
    }
}
