<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Generator;

use Kreitje\UddfGenerator\XmlSerializable;

final class Generator implements XmlSerializable
{
    public function __construct(
        public readonly string $name,
        public readonly string $version,
        public readonly ?\DateTimeImmutable $datetime = null,
        public readonly ?Manufacturer $manufacturer = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('generator');

        $el->appendChild($doc->createElement('name', $this->name));
        $el->appendChild($doc->createElement('version', $this->version));

        if ($this->manufacturer !== null) {
            $el->appendChild($this->manufacturer->toXml($doc));
        }

        $datetime = $this->datetime ?? new \DateTimeImmutable();
        $el->appendChild($doc->createElement('datetime', $datetime->format('Y-m-d\TH:i:s')));

        return $el;
    }
}
