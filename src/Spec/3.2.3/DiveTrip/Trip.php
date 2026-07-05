<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveTrip;

use Kreitje\UddfGenerator\Spec\V323\Common\Rating;
use Kreitje\UddfGenerator\XmlSerializable;

final class Trip implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        /** @var Rating[] */
        public readonly array $ratings = [],
        /** @var TripPart[] */
        public readonly array $tripParts = [],
    ) {
        if ($this->tripParts === []) {
            throw new \InvalidArgumentException('A trip requires at least one trip part.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('trip');
        $el->setAttribute('id', $this->id);

        $el->appendChild($doc->createElement('name', $this->name));

        foreach ($this->aliasNames as $aliasName) {
            $el->appendChild($doc->createElement('aliasname', $aliasName));
        }

        foreach ($this->ratings as $rating) {
            $el->appendChild($rating->toXml($doc));
        }

        foreach ($this->tripParts as $tripPart) {
            $el->appendChild($tripPart->toXml($doc));
        }

        return $el;
    }
}
