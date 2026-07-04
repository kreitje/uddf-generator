<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\XmlSerializable;

final class InformationAfterDive implements XmlSerializable
{
    public function __construct(
        public readonly float $greatestDepth,
        public readonly float $diveDuration,
        public readonly ?float $averageDepth = null,
        public readonly ?Notes $notes = null,
    ) {
        if ($this->greatestDepth < 0.0) {
            throw new \InvalidArgumentException('Greatest depth cannot be negative.');
        }

        if ($this->diveDuration < 0.0) {
            throw new \InvalidArgumentException('Dive duration cannot be negative.');
        }
    }

    public static function fromWaypoints(Waypoint ...$waypoints): self
    {
        if (count($waypoints) < 2) {
            throw new \InvalidArgumentException('At least two waypoints are required to compute dive info.');
        }

        $maxDepth = 0.0;
        $totalDepth = 0.0;
        $lastTime = 0.0;

        foreach ($waypoints as $waypoint) {
            if ($waypoint->depth > $maxDepth) {
                $maxDepth = $waypoint->depth;
            }
            $totalDepth += $waypoint->depth;
            $lastTime = max($lastTime, $waypoint->diveTime);
        }

        return new self(
            greatestDepth: $maxDepth,
            diveDuration: $lastTime,
            averageDepth: $totalDepth / count($waypoints),
        );
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('informationafterdive');

        $el->appendChild($doc->createElement('greatestdepth', (string) $this->greatestDepth));
        $el->appendChild($doc->createElement('diveduration', (string) $this->diveDuration));

        if ($this->averageDepth !== null) {
            $el->appendChild($doc->createElement('averagedepth', (string) $this->averageDepth));
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
