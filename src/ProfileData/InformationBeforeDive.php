<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\Common\Price;
use Kreitje\UddfGenerator\Enum\Apparatus;
use Kreitje\UddfGenerator\Enum\Platform;
use Kreitje\UddfGenerator\Enum\Purpose;
use Kreitje\UddfGenerator\Enum\StateOfRestBeforeDive;
use Kreitje\UddfGenerator\XmlSerializable;

final class InformationBeforeDive implements XmlSerializable
{
    public function __construct(
        public readonly \DateTimeImmutable $datetime,
        public readonly ?int $diveNumber = null,
        public readonly ?string $diveSiteRef = null,
        public readonly ?int $diveNumberOfDay = null,
        public readonly ?int $internalDiveNumber = null,
        public readonly ?float $airTemperature = null,
        public readonly ?SurfaceInterval $surfaceIntervalBeforeDive = null,
        public readonly ?float $altitude = null,
        public readonly ?EquipmentUsed $equipmentUsed = null,
        public readonly ?Apparatus $apparatus = null,
        public readonly ?Platform $platform = null,
        public readonly ?Purpose $purpose = null,
        public readonly ?StateOfRestBeforeDive $stateOfRestBeforeDive = null,
        public readonly ?string $tripMembershipRef = null,
        /** @var Drug[] */
        public readonly array $alcoholBeforeDive = [],
        /** @var Drug[] */
        public readonly array $medicalBeforeDive = [],
        public readonly bool $noSuit = false,
        public readonly ?Price $price = null,
        public readonly ?InputProfile $inputProfile = null,
        /** @var Waypoint[] */
        public readonly array $plannedProfile = [],
        public readonly ?float $surfacePressure = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('informationbeforedive');

        if ($this->diveSiteRef !== null) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $this->diveSiteRef);
            $el->appendChild($link);
        }

        if ($this->diveNumber !== null) {
            $el->appendChild($doc->createElement('divenumber', (string) $this->diveNumber));
        }

        if ($this->diveNumberOfDay !== null) {
            $el->appendChild($doc->createElement('divenumberofday', (string) $this->diveNumberOfDay));
        }

        if ($this->internalDiveNumber !== null) {
            $el->appendChild($doc->createElement('internaldivenumber', (string) $this->internalDiveNumber));
        }

        $el->appendChild($doc->createElement('datetime', $this->datetime->format('Y-m-d\TH:i:s')));

        if ($this->airTemperature !== null) {
            $el->appendChild($doc->createElement('airtemperature', (string) $this->airTemperature));
        }

        if ($this->surfaceIntervalBeforeDive !== null) {
            $el->appendChild($this->surfaceIntervalBeforeDive->toXml($doc, 'surfaceintervalbeforedive'));
        }

        if ($this->altitude !== null) {
            $el->appendChild($doc->createElement('altitude', (string) $this->altitude));
        }

        if ($this->equipmentUsed !== null) {
            $el->appendChild($this->equipmentUsed->toXml($doc));
        }

        if ($this->apparatus !== null) {
            $el->appendChild($doc->createElement('apparatus', $this->apparatus->value));
        }

        if ($this->platform !== null) {
            $el->appendChild($doc->createElement('platform', $this->platform->value));
        }

        if ($this->purpose !== null) {
            $el->appendChild($doc->createElement('purpose', $this->purpose->value));
        }

        if ($this->stateOfRestBeforeDive !== null) {
            $el->appendChild($doc->createElement('stateofrestbeforedive', $this->stateOfRestBeforeDive->value));
        }

        if ($this->tripMembershipRef !== null) {
            $tripMembership = $doc->createElement('tripmembership');
            $tripMembership->setAttribute('ref', $this->tripMembershipRef);
            $el->appendChild($tripMembership);
        }

        if ($this->alcoholBeforeDive !== []) {
            $alcohol = $doc->createElement('alcoholbeforedive');
            foreach ($this->alcoholBeforeDive as $drink) {
                $alcohol->appendChild($drink->toXml($doc, 'drink'));
            }
            $el->appendChild($alcohol);
        }

        if ($this->medicalBeforeDive !== []) {
            $medical = $doc->createElement('medicalbeforedive');
            foreach ($this->medicalBeforeDive as $medicine) {
                $medical->appendChild($medicine->toXml($doc, 'medicine'));
            }
            $el->appendChild($medical);
        }

        if ($this->noSuit) {
            $el->appendChild($doc->createElement('nosuit'));
        }

        if ($this->price !== null) {
            $el->appendChild($this->price->toXml($doc, 'price'));
        }

        if ($this->inputProfile !== null) {
            $el->appendChild($this->inputProfile->toXml($doc));
        }

        if ($this->plannedProfile !== []) {
            $plannedProfile = $doc->createElement('plannedprofile');
            foreach ($this->plannedProfile as $waypoint) {
                $plannedProfile->appendChild($waypoint->toXml($doc));
            }
            $el->appendChild($plannedProfile);
        }

        if ($this->surfacePressure !== null) {
            $el->appendChild($doc->createElement('surfacepressure', (string) $this->surfacePressure));
        }

        return $el;
    }
}
