<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\ProfileData;

final class SurfaceInterval
{
    public function __construct(
        public readonly bool $infinity = false,
        public readonly ?int $passedTime = null,
        /** @var WayAltitude[] */
        public readonly array $wayAltitudes = [],
        public readonly ?ExposureToAltitude $exposureToAltitude = null,
    ) {
        if ($this->infinity === ($this->passedTime !== null)) {
            throw new \InvalidArgumentException('SurfaceInterval must have exactly one of infinity or passedTime.');
        }
    }

    public function toXml(\DOMDocument $doc, string $elementName): \DOMElement
    {
        $el = $doc->createElement($elementName);

        if ($this->infinity) {
            $el->appendChild($doc->createElement('infinity'));
        } else {
            $el->appendChild($doc->createElement('passedtime', (string) $this->passedTime));
        }

        foreach ($this->wayAltitudes as $wayAltitude) {
            $el->appendChild($wayAltitude->toXml($doc));
        }

        if ($this->exposureToAltitude !== null) {
            $el->appendChild($this->exposureToAltitude->toXml($doc));
        }

        return $el;
    }
}
