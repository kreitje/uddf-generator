<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveSite;

final class SingleLifeForm
{
    public function __construct(
        /** @var Species[] */
        public readonly array $species = [],
    ) {}

    public function toXml(\DOMDocument $doc, string $elementName): \DOMElement
    {
        $el = $doc->createElement($elementName);

        foreach ($this->species as $species) {
            $el->appendChild($species->toXml($doc));
        }

        return $el;
    }
}
