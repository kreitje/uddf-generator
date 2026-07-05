<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\TableGeneration;

use Kreitje\UddfGenerator\ProfileData\Waypoint;
use Kreitje\UddfGenerator\XmlSerializable;

final class MixChange implements XmlSerializable
{
    public function __construct(
        /** @var Waypoint[] */
        public readonly array $ascent = [],
        /** @var Waypoint[] */
        public readonly array $descent = [],
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('mixchange');

        if ($this->ascent !== []) {
            $ascent = $doc->createElement('ascent');
            foreach ($this->ascent as $waypoint) {
                $ascent->appendChild($waypoint->toXml($doc));
            }
            $el->appendChild($ascent);
        }

        if ($this->descent !== []) {
            $descent = $doc->createElement('descent');
            foreach ($this->descent as $waypoint) {
                $descent->appendChild($waypoint->toXml($doc));
            }
            $el->appendChild($descent);
        }

        return $el;
    }
}
