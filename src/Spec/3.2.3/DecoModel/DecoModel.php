<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DecoModel;

use Kreitje\UddfGenerator\XmlSerializable;

final class DecoModel implements XmlSerializable
{
    public function __construct(
        public readonly Buehlmann $buehlmann,
        public readonly Rgbm $rgbm,
        public readonly Vpm $vpm,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('decomodel');

        $el->appendChild($this->buehlmann->toXml($doc));
        $el->appendChild($this->rgbm->toXml($doc));
        $el->appendChild($this->vpm->toXml($doc));

        return $el;
    }
}
