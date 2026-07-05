<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\XmlSerializable;

final class Equipment implements XmlSerializable
{
    public function __construct(
        /** @var EquipmentPiece[] */
        public readonly array $boots = [],
        /** @var EquipmentPiece[] */
        public readonly array $buoyancyControlDevices = [],
        /** @var Camera[] */
        public readonly array $cameras = [],
        /** @var EquipmentPiece[] */
        public readonly array $compasses = [],
        /** @var EquipmentPiece[] */
        public readonly array $compressors = [],
        /** @var EquipmentPiece[] */
        public readonly array $diveComputers = [],
        /** @var EquipmentConfiguration[] */
        public readonly array $equipmentConfigurations = [],
        /** @var EquipmentPiece[] */
        public readonly array $fins = [],
        /** @var EquipmentPiece[] */
        public readonly array $gloves = [],
        /** @var EquipmentPiece[] */
        public readonly array $knives = [],
        /** @var EquipmentPiece[] */
        public readonly array $lead = [],
        /** @var EquipmentPiece[] */
        public readonly array $lights = [],
        /** @var EquipmentPiece[] */
        public readonly array $masks = [],
        /** @var Rebreather[] */
        public readonly array $rebreathers = [],
        /** @var EquipmentPiece[] */
        public readonly array $regulators = [],
        /** @var EquipmentPiece[] */
        public readonly array $scooters = [],
        /** @var Suit[] */
        public readonly array $suits = [],
        /** @var Tank[] */
        public readonly array $tanks = [],
        /** @var EquipmentPiece[] */
        public readonly array $variousPieces = [],
        /** @var Videocamera[] */
        public readonly array $videocameras = [],
        /** @var EquipmentPiece[] */
        public readonly array $watches = [],
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('equipment');

        foreach ($this->boots as $piece) {
            $el->appendChild($piece->toXml($doc, 'boots'));
        }

        foreach ($this->buoyancyControlDevices as $piece) {
            $el->appendChild($piece->toXml($doc, 'buoyancycontroldevice'));
        }

        foreach ($this->cameras as $camera) {
            $el->appendChild($camera->toXml($doc));
        }

        foreach ($this->compasses as $piece) {
            $el->appendChild($piece->toXml($doc, 'compass'));
        }

        foreach ($this->compressors as $piece) {
            $el->appendChild($piece->toXml($doc, 'compressor'));
        }

        foreach ($this->diveComputers as $piece) {
            $el->appendChild($piece->toXml($doc, 'divecomputer'));
        }

        foreach ($this->equipmentConfigurations as $configuration) {
            $el->appendChild($configuration->toXml($doc));
        }

        foreach ($this->fins as $piece) {
            $el->appendChild($piece->toXml($doc, 'fins'));
        }

        foreach ($this->gloves as $piece) {
            $el->appendChild($piece->toXml($doc, 'gloves'));
        }

        foreach ($this->knives as $piece) {
            $el->appendChild($piece->toXml($doc, 'knife'));
        }

        foreach ($this->lead as $piece) {
            $el->appendChild($piece->toXml($doc, 'lead'));
        }

        foreach ($this->lights as $piece) {
            $el->appendChild($piece->toXml($doc, 'light'));
        }

        foreach ($this->masks as $piece) {
            $el->appendChild($piece->toXml($doc, 'mask'));
        }

        foreach ($this->rebreathers as $rebreather) {
            $el->appendChild($rebreather->toXml($doc));
        }

        foreach ($this->regulators as $piece) {
            $el->appendChild($piece->toXml($doc, 'regulator'));
        }

        foreach ($this->scooters as $piece) {
            $el->appendChild($piece->toXml($doc, 'scooter'));
        }

        foreach ($this->suits as $suit) {
            $el->appendChild($suit->toXml($doc));
        }

        foreach ($this->tanks as $tank) {
            $el->appendChild($tank->toXml($doc));
        }

        foreach ($this->variousPieces as $piece) {
            $el->appendChild($piece->toXml($doc, 'variouspieces'));
        }

        foreach ($this->videocameras as $videocamera) {
            $el->appendChild($videocamera->toXml($doc));
        }

        foreach ($this->watches as $piece) {
            $el->appendChild($piece->toXml($doc, 'watch'));
        }

        return $el;
    }
}
