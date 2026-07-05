<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\Enum\SuitType;
use Kreitje\UddfGenerator\Generator\Manufacturer;
use Kreitje\UddfGenerator\XmlSerializable;

final class Suit implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?string $linkRef = null,
        public readonly ?Manufacturer $manufacturer = null,
        public readonly ?string $model = null,
        public readonly ?string $serialNumber = null,
        public readonly ?Purchase $purchase = null,
        public readonly ?int $serviceInterval = null,
        public readonly ?\DateTimeImmutable $nextServiceDate = null,
        public readonly ?Notes $notes = null,
        public readonly ?SuitType $suitType = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $piece = new EquipmentPiece(
            id: $this->id,
            name: $this->name,
            aliasNames: $this->aliasNames,
            linkRef: $this->linkRef,
            manufacturer: $this->manufacturer,
            model: $this->model,
            serialNumber: $this->serialNumber,
            purchase: $this->purchase,
            serviceInterval: $this->serviceInterval,
            nextServiceDate: $this->nextServiceDate,
            notes: $this->notes,
        );

        $el = $piece->toXml($doc, 'suit');

        if ($this->suitType !== null) {
            $el->appendChild($doc->createElement('suittype', $this->suitType->value));
        }

        return $el;
    }
}
