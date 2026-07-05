<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveSite;

use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\Enum\AbundanceOccurence;
use Kreitje\UddfGenerator\Enum\AbundanceQuality;
use Kreitje\UddfGenerator\Enum\Dominance;
use Kreitje\UddfGenerator\Enum\GlobalLightIntensity;
use Kreitje\UddfGenerator\Enum\LifeStage;
use Kreitje\UddfGenerator\Enum\Sex;
use Kreitje\UddfGenerator\XmlSerializable;

final class Species implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $trivialName = null,
        public readonly ?string $scientificName = null,
        public readonly ?int $abundanceValue = null,
        public readonly ?AbundanceQuality $abundanceQuality = null,
        public readonly ?AbundanceOccurence $abundanceOccurence = null,
        public readonly ?Sex $sex = null,
        public readonly ?LifeStage $lifeStage = null,
        public readonly ?GlobalLightIntensity $lightIntensity = null,
        public readonly ?float $lightIntensityLux = null,
        public readonly ?int $age = null,
        public readonly ?Dominance $dominance = null,
        public readonly ?float $size = null,
        public readonly ?Notes $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('species');
        $el->setAttribute('id', $this->id);

        if ($this->trivialName !== null) {
            $el->appendChild($doc->createElement('trivialname', $this->trivialName));
        }

        if ($this->scientificName !== null) {
            $el->appendChild($doc->createElement('scientificname', $this->scientificName));
        }

        if ($this->abundanceValue !== null) {
            $abundance = $doc->createElement('abundance', (string) $this->abundanceValue);
            if ($this->abundanceQuality !== null) {
                $abundance->setAttribute('quality', $this->abundanceQuality->value);
            }
            if ($this->abundanceOccurence !== null) {
                $abundance->setAttribute('occurence', $this->abundanceOccurence->value);
            }
            $el->appendChild($abundance);
        }

        if ($this->sex !== null) {
            $el->appendChild($doc->createElement('sex', $this->sex->value));
        }

        if ($this->lifeStage !== null) {
            $el->appendChild($doc->createElement('lifestage', $this->lifeStage->value));
        }

        if ($this->lightIntensity !== null) {
            $lightIntensity = $doc->createElement('lightintensity', $this->lightIntensity->value);
            if ($this->lightIntensityLux !== null) {
                $lightIntensity->setAttribute('lux', (string) $this->lightIntensityLux);
            }
            $el->appendChild($lightIntensity);
        }

        if ($this->age !== null) {
            $el->appendChild($doc->createElement('age', (string) $this->age));
        }

        if ($this->dominance !== null) {
            $el->appendChild($doc->createElement('dominance', $this->dominance->value));
        }

        if ($this->size !== null) {
            $el->appendChild($doc->createElement('size', (string) $this->size));
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
