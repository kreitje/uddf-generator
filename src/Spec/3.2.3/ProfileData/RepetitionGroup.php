<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\ProfileData;

use Kreitje\UddfGenerator\XmlSerializable;

final class RepetitionGroup implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        /** @var Dive[] */
        public readonly array $dives,
    ) {
        if ($this->dives === []) {
            throw new \InvalidArgumentException('A repetition group must contain at least one dive.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('repetitiongroup');
        $el->setAttribute('id', $this->id);

        foreach ($this->dives as $dive) {
            $el->appendChild($dive->toXml($doc));
        }

        return $el;
    }
}
