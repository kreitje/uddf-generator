<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\XmlSerializable;

final class Videocamera implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly ?EquipmentPiece $body = null,
        public readonly ?EquipmentPiece $lens = null,
        public readonly ?EquipmentPiece $housing = null,
        public readonly ?EquipmentPiece $light = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('videocamera');
        $el->setAttribute('id', $this->id);

        if ($this->body !== null) {
            $el->appendChild($this->body->toXml($doc, 'body'));
        }

        if ($this->lens !== null) {
            $el->appendChild($this->lens->toXml($doc, 'lens'));
        }

        if ($this->housing !== null) {
            $el->appendChild($this->housing->toXml($doc, 'housing'));
        }

        if ($this->light !== null) {
            $el->appendChild($this->light->toXml($doc, 'light'));
        }

        return $el;
    }
}
