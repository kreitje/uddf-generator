<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\XmlSerializable;

final class InputProfile implements XmlSerializable
{
    public function __construct(
        /** @var string[] */
        public readonly array $linkRefs,
        /** @var Waypoint[] */
        public readonly array $waypoints,
    ) {
        if ($this->waypoints === []) {
            throw new \InvalidArgumentException('InputProfile requires at least one waypoint.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('inputprofile');

        foreach ($this->linkRefs as $linkRef) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $linkRef);
            $el->appendChild($link);
        }

        foreach ($this->waypoints as $waypoint) {
            $el->appendChild($waypoint->toXml($doc));
        }

        return $el;
    }
}
