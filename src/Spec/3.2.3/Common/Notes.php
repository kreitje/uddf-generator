<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Common;

use Kreitje\UddfGenerator\XmlSerializable;

final class Notes implements XmlSerializable
{
    public function __construct(
        /** @var string[] */
        public readonly array $paragraphs = [],
        /** @var string[] */
        public readonly array $linkRefs = [],
    ) {
        if ($this->paragraphs === [] && $this->linkRefs === []) {
            throw new \InvalidArgumentException('Notes must contain at least one paragraph or link.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('notes');

        foreach ($this->paragraphs as $paragraph) {
            $el->appendChild($doc->createElement('para', $paragraph));
        }

        foreach ($this->linkRefs as $linkRef) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $linkRef);
            $el->appendChild($link);
        }

        return $el;
    }
}
