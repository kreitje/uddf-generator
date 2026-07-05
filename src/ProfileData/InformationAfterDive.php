<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\Common\Rating;
use Kreitje\UddfGenerator\Enum\Current;
use Kreitje\UddfGenerator\Enum\DivePlan;
use Kreitje\UddfGenerator\Enum\DiveTable;
use Kreitje\UddfGenerator\Enum\EquipmentMalfunction;
use Kreitje\UddfGenerator\Enum\GlobalAlarm;
use Kreitje\UddfGenerator\Enum\Problems;
use Kreitje\UddfGenerator\Enum\Program;
use Kreitje\UddfGenerator\Enum\ThermalComfort;
use Kreitje\UddfGenerator\Enum\Workload;
use Kreitje\UddfGenerator\XmlSerializable;

final class InformationAfterDive implements XmlSerializable
{
    public function __construct(
        public readonly float $greatestDepth,
        public readonly float $diveDuration,
        public readonly ?float $averageDepth = null,
        public readonly ?Notes $notes = null,
        public readonly ?SurfaceInterval $surfaceIntervalAfterDive = null,
        public readonly ?float $lowestTemperature = null,
        public readonly ?float $visibility = null,
        public readonly ?Current $current = null,
        public readonly ?DivePlan $divePlan = null,
        public readonly ?EquipmentMalfunction $equipmentMalfunction = null,
        public readonly ?float $pressureDrop = null,
        public readonly ?Problems $problems = null,
        public readonly ?Program $program = null,
        public readonly ?ThermalComfort $thermalComfort = null,
        public readonly ?Workload $workload = null,
        public readonly ?float $desaturationTime = null,
        public readonly ?float $noFlightTime = null,
        public readonly ?Rating $rating = null,
        /** @var Notes[] */
        public readonly array $anySymptoms = [],
        public readonly ?DiveTable $diveTable = null,
        /** @var GlobalAlarm[] */
        public readonly array $globalAlarmsGiven = [],
        public readonly ?float $highestPo2 = null,
        public readonly ?Observations $observations = null,
    ) {
        if ($this->greatestDepth < 0.0) {
            throw new \InvalidArgumentException('Greatest depth cannot be negative.');
        }

        if ($this->diveDuration < 0.0) {
            throw new \InvalidArgumentException('Dive duration cannot be negative.');
        }
    }

    public static function fromWaypoints(Waypoint ...$waypoints): self
    {
        if (count($waypoints) < 2) {
            throw new \InvalidArgumentException('At least two waypoints are required to compute dive info.');
        }

        $maxDepth = 0.0;
        $totalDepth = 0.0;
        $lastTime = 0.0;

        foreach ($waypoints as $waypoint) {
            if ($waypoint->depth > $maxDepth) {
                $maxDepth = $waypoint->depth;
            }
            $totalDepth += $waypoint->depth;
            $lastTime = max($lastTime, $waypoint->diveTime);
        }

        return new self(
            greatestDepth: $maxDepth,
            diveDuration: $lastTime,
            averageDepth: $totalDepth / count($waypoints),
        );
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('informationafterdive');

        if ($this->surfaceIntervalAfterDive !== null) {
            $el->appendChild($this->surfaceIntervalAfterDive->toXml($doc, 'surfaceintervalafterdive'));
        }

        if ($this->lowestTemperature !== null) {
            $el->appendChild($doc->createElement('lowesttemperature', (string) $this->lowestTemperature));
        }

        $el->appendChild($doc->createElement('greatestdepth', (string) $this->greatestDepth));

        if ($this->visibility !== null) {
            $el->appendChild($doc->createElement('visibility', (string) $this->visibility));
        }

        if ($this->current !== null) {
            $el->appendChild($doc->createElement('current', $this->current->value));
        }

        if ($this->divePlan !== null) {
            $el->appendChild($doc->createElement('diveplan', $this->divePlan->value));
        }

        if ($this->equipmentMalfunction !== null) {
            $el->appendChild($doc->createElement('equipmentmalfunction', $this->equipmentMalfunction->value));
        }

        if ($this->pressureDrop !== null) {
            $el->appendChild($doc->createElement('pressuredrop', (string) $this->pressureDrop));
        }

        if ($this->problems !== null) {
            $el->appendChild($doc->createElement('problems', $this->problems->value));
        }

        if ($this->program !== null) {
            $el->appendChild($doc->createElement('program', $this->program->value));
        }

        if ($this->thermalComfort !== null) {
            $el->appendChild($doc->createElement('thermalcomfort', $this->thermalComfort->value));
        }

        if ($this->workload !== null) {
            $el->appendChild($doc->createElement('workload', $this->workload->value));
        }

        if ($this->desaturationTime !== null) {
            $el->appendChild($doc->createElement('desaturationtime', (string) $this->desaturationTime));
        }

        if ($this->noFlightTime !== null) {
            $el->appendChild($doc->createElement('noflighttime', (string) $this->noFlightTime));
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        if ($this->rating !== null) {
            $el->appendChild($this->rating->toXml($doc));
        }

        if ($this->anySymptoms !== []) {
            $anySymptoms = $doc->createElement('anysymptoms');
            foreach ($this->anySymptoms as $notes) {
                $anySymptoms->appendChild($notes->toXml($doc));
            }
            $el->appendChild($anySymptoms);
        }

        $el->appendChild($doc->createElement('diveduration', (string) $this->diveDuration));

        if ($this->diveTable !== null) {
            $el->appendChild($doc->createElement('divetable', $this->diveTable->value));
        }

        if ($this->globalAlarmsGiven !== []) {
            $globalAlarmsGiven = $doc->createElement('globalalarmsgiven');
            foreach ($this->globalAlarmsGiven as $globalAlarm) {
                $globalAlarmsGiven->appendChild($doc->createElement('globalalarm', $globalAlarm->value));
            }
            $el->appendChild($globalAlarmsGiven);
        }

        if ($this->highestPo2 !== null) {
            $el->appendChild($doc->createElement('highestpo2', (string) $this->highestPo2));
        }

        if ($this->observations !== null) {
            $el->appendChild($this->observations->toXml($doc));
        }

        if ($this->averageDepth !== null) {
            $el->appendChild($doc->createElement('averagedepth', (string) $this->averageDepth));
        }

        return $el;
    }
}
