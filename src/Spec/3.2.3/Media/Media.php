<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Media;

final class Media
{
    public function __construct(
        public readonly string $id,
        public readonly string $objectName,
        public readonly ?string $title = null,
    ) {}

    public function toXml(\DOMDocument $doc, string $elementName): \DOMElement
    {
        $el = $doc->createElement($elementName);
        $el->setAttribute('id', $this->id);

        if ($this->title !== null) {
            $el->appendChild($doc->createElement('title', $this->title));
        }

        $el->appendChild($doc->createElement('objectname', $this->objectName));

        return $el;
    }
}
