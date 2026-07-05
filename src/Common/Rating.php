<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Common;

use Kreitje\UddfGenerator\XmlSerializable;

final class Rating implements XmlSerializable
{
    public function __construct(
        public readonly int $value,
        public readonly ?\DateTimeImmutable $datetime = null,
    ) {
        if ($this->value < 1 || $this->value > 10) {
            throw new \InvalidArgumentException('Rating value must be between 1 and 10.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('rating');

        if ($this->datetime !== null) {
            $el->appendChild($doc->createElement('datetime', $this->datetime->format('Y-m-d\TH:i:s')));
        }

        $el->appendChild($doc->createElement('ratingvalue', (string) $this->value));

        return $el;
    }
}
