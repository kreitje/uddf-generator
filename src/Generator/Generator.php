<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Generator;

use Kreitje\UddfGenerator\Enum\GeneratorType;
use Kreitje\UddfGenerator\XmlSerializable;

final class Generator implements XmlSerializable
{
    public function __construct(
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?GeneratorType $type = null,
        public readonly ?string $linkRef = null,
        public readonly ?Manufacturer $manufacturer = null,
        public readonly ?string $version = null,
        public readonly ?\DateTimeImmutable $datetime = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('generator');

        $el->appendChild($doc->createElement('name', $this->name));

        foreach ($this->aliasNames as $aliasName) {
            $el->appendChild($doc->createElement('aliasname', $aliasName));
        }

        if ($this->type !== null) {
            $el->appendChild($doc->createElement('type', $this->type->value));
        }

        if ($this->linkRef !== null) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $this->linkRef);
            $el->appendChild($link);
        }

        if ($this->manufacturer !== null) {
            $el->appendChild($this->manufacturer->toXml($doc));
        }

        if ($this->version !== null) {
            $el->appendChild($doc->createElement('version', $this->version));
        }

        $datetime = $this->datetime ?? new \DateTimeImmutable();
        $el->appendChild($doc->createElement('datetime', $datetime->format('Y-m-d\TH:i:s')));

        return $el;
    }
}
