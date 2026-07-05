<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\XmlSerializable;

final class EquipmentUsed implements XmlSerializable
{
    public function __construct(
        public readonly ?float $leadQuantity = null,
        /** @var string[] */
        public readonly array $linkRefs = [],
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('equipmentused');

        if ($this->leadQuantity !== null) {
            $el->appendChild($doc->createElement('leadquantity', (string) $this->leadQuantity));
        }

        foreach ($this->linkRefs as $linkRef) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $linkRef);
            $el->appendChild($link);
        }

        return $el;
    }
}
