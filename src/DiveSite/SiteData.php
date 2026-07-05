<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveSite;

use Kreitje\UddfGenerator\Enum\GlobalLightIntensity;
use Kreitje\UddfGenerator\XmlSerializable;

final class SiteData implements XmlSerializable
{
    public function __construct(
        public readonly ?float $areaLength = null,
        public readonly ?float $areaWidth = null,
        public readonly ?float $averageVisibility = null,
        public readonly ?string $bottom = null,
        public readonly ?float $density = null,
        public readonly ?int $difficulty = null,
        public readonly ?GlobalLightIntensity $globalLightIntensity = null,
        public readonly ?float $maximumDepth = null,
        public readonly ?float $maximumVisibility = null,
        public readonly ?float $minimumDepth = null,
        public readonly ?float $minimumVisibility = null,
        public readonly ?string $terrain = null,
        /** @var Wreck[] */
        public readonly array $wrecks = [],
        public readonly ?Cave $cave = null,
        public readonly ?Indoor $indoor = null,
        public readonly ?Place $lake = null,
        public readonly ?Place $river = null,
        public readonly ?Place $shore = null,
    ) {
        if ($this->difficulty !== null && ($this->difficulty < 1 || $this->difficulty > 10)) {
            throw new \InvalidArgumentException('Difficulty must be between 1 and 10.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('sitedata');

        if ($this->areaLength !== null) {
            $el->appendChild($doc->createElement('arealength', (string) $this->areaLength));
        }

        if ($this->areaWidth !== null) {
            $el->appendChild($doc->createElement('areawidth', (string) $this->areaWidth));
        }

        if ($this->averageVisibility !== null) {
            $el->appendChild($doc->createElement('averagevisibility', (string) $this->averageVisibility));
        }

        if ($this->bottom !== null) {
            $el->appendChild($doc->createElement('bottom', $this->bottom));
        }

        if ($this->density !== null) {
            $el->appendChild($doc->createElement('density', (string) $this->density));
        }

        if ($this->difficulty !== null) {
            $el->appendChild($doc->createElement('difficulty', (string) $this->difficulty));
        }

        if ($this->globalLightIntensity !== null) {
            $el->appendChild($doc->createElement('globallightintensity', $this->globalLightIntensity->value));
        }

        if ($this->maximumDepth !== null) {
            $el->appendChild($doc->createElement('maximumdepth', (string) $this->maximumDepth));
        }

        if ($this->maximumVisibility !== null) {
            $el->appendChild($doc->createElement('maximumvisibility', (string) $this->maximumVisibility));
        }

        if ($this->minimumDepth !== null) {
            $el->appendChild($doc->createElement('minimumdepth', (string) $this->minimumDepth));
        }

        if ($this->minimumVisibility !== null) {
            $el->appendChild($doc->createElement('minimumvisibility', (string) $this->minimumVisibility));
        }

        if ($this->terrain !== null) {
            $el->appendChild($doc->createElement('terrain', $this->terrain));
        }

        foreach ($this->wrecks as $wreck) {
            $el->appendChild($wreck->toXml($doc));
        }

        if ($this->cave !== null) {
            $el->appendChild($this->cave->toXml($doc));
        }

        if ($this->indoor !== null) {
            $el->appendChild($this->indoor->toXml($doc));
        }

        if ($this->lake !== null) {
            $el->appendChild($this->lake->toXml($doc, 'lake'));
        }

        if ($this->river !== null) {
            $el->appendChild($this->river->toXml($doc, 'river'));
        }

        if ($this->shore !== null) {
            $el->appendChild($this->shore->toXml($doc, 'shore'));
        }

        return $el;
    }
}
