<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Media;

use Kreitje\UddfGenerator\Spec\V323\Enum\MeteringMethod;
use Kreitje\UddfGenerator\XmlSerializable;

final class ImageData implements XmlSerializable
{
    public function __construct(
        public readonly ?float $aperture = null,
        public readonly ?\DateTimeImmutable $datetime = null,
        public readonly ?float $exposureCompensation = null,
        public readonly ?int $filmSpeed = null,
        public readonly ?float $focalLength = null,
        public readonly ?float $focusingDistance = null,
        public readonly ?MeteringMethod $meteringMethod = null,
        public readonly ?float $shutterSpeed = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('imagedata');

        if ($this->aperture !== null) {
            $el->appendChild($doc->createElement('aperture', (string) $this->aperture));
        }

        if ($this->datetime !== null) {
            $el->appendChild($doc->createElement('datetime', $this->datetime->format('Y-m-d\TH:i:s')));
        }

        if ($this->exposureCompensation !== null) {
            $el->appendChild($doc->createElement('exposurecompensation', (string) $this->exposureCompensation));
        }

        if ($this->filmSpeed !== null) {
            $el->appendChild($doc->createElement('filmspeed', (string) $this->filmSpeed));
        }

        if ($this->focalLength !== null) {
            $el->appendChild($doc->createElement('focallength', (string) $this->focalLength));
        }

        if ($this->focusingDistance !== null) {
            $el->appendChild($doc->createElement('focusingdistance', (string) $this->focusingDistance));
        }

        if ($this->meteringMethod !== null) {
            $el->appendChild($doc->createElement('meteringmethod', $this->meteringMethod->value));
        }

        if ($this->shutterSpeed !== null) {
            $el->appendChild($doc->createElement('shutterspeed', (string) $this->shutterSpeed));
        }

        return $el;
    }
}
