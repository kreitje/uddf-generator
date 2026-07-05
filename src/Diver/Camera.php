<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\XmlSerializable;

final class Camera implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        /** @var EquipmentPiece[] */
        public readonly array $bodies = [],
        /** @var EquipmentPiece[] */
        public readonly array $lenses = [],
        /** @var EquipmentPiece[] */
        public readonly array $housings = [],
        /** @var EquipmentPiece[] */
        public readonly array $flashes = [],
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('camera');
        $el->setAttribute('id', $this->id);

        foreach ($this->bodies as $body) {
            $el->appendChild($body->toXml($doc, 'body'));
        }

        foreach ($this->lenses as $lens) {
            $el->appendChild($lens->toXml($doc, 'lens'));
        }

        foreach ($this->housings as $housing) {
            $el->appendChild($housing->toXml($doc, 'housing'));
        }

        foreach ($this->flashes as $flash) {
            $el->appendChild($flash->toXml($doc, 'flash'));
        }

        return $el;
    }
}
