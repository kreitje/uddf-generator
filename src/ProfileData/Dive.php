<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\XmlSerializable;

final class Dive implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly InformationBeforeDive $informationBeforeDive,
        /** @var Waypoint[] */
        public readonly array $samples,
        public readonly ?InformationAfterDive $informationAfterDive = null,
    ) {
        if (count($this->samples) < 2) {
            throw new \InvalidArgumentException('A dive must have at least two waypoints.');
        }
    }

    public function getInformationAfterDive(): InformationAfterDive
    {
        return $this->informationAfterDive ?? InformationAfterDive::fromWaypoints(...$this->samples);
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('dive');
        $el->setAttribute('id', $this->id);

        $el->appendChild($this->informationBeforeDive->toXml($doc));

        $samplesEl = $doc->createElement('samples');
        foreach ($this->samples as $waypoint) {
            $samplesEl->appendChild($waypoint->toXml($doc));
        }
        $el->appendChild($samplesEl);

        $el->appendChild($this->getInformationAfterDive()->toXml($doc));

        return $el;
    }
}
