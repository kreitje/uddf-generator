<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\Enum\TankMaterial;
use Kreitje\UddfGenerator\Generator\Manufacturer;
use Kreitje\UddfGenerator\XmlSerializable;

final class Tank implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?float $volume = null,
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
        public readonly ?TankMaterial $tankMaterial = null,
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

        $el = $piece->toXml($doc, 'tank');

        if ($this->tankMaterial !== null) {
            $el->appendChild($doc->createElement('tankmaterial', $this->tankMaterial->value));
        }

        if ($this->volume !== null) {
            $el->appendChild($doc->createElement('tankvolume', (string) $this->volume));
        }

        return $el;
    }
}
