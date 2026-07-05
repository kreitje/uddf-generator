<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\Enum\DiveMode;
use Kreitje\UddfGenerator\XmlSerializable;

final class Waypoint implements XmlSerializable
{
    public function __construct(
        public readonly float $depth,
        public readonly float $diveTime,
        public readonly ?float $temperature = null,
        /** @var TankPressureReading[] */
        public readonly array $tankPressures = [],
        public readonly ?string $switchMixRef = null,
        /** @var Alarm[] */
        public readonly array $alarms = [],
        /** @var BatteryChargeCondition[] */
        public readonly array $batteryChargeConditions = [],
        public readonly ?float $cns = null,
        /** @var DecoStop[] */
        public readonly array $decoStops = [],
        public readonly ?float $bodyTemperature = null,
        public readonly ?float $calculatedPo2 = null,
        public readonly ?float $heading = null,
        public readonly ?float $heartRate = null,
        public readonly ?float $otu = null,
        public readonly ?float $pulseRate = null,
        public readonly ?float $remainingBottomTime = null,
        public readonly ?float $remainingO2Time = null,
        public readonly ?string $setMarker = null,
        public readonly ?SetPo2 $setPo2 = null,
        public readonly ?DiveMode $diveMode = null,
        public readonly ?GradientFactor $gradientFactor = null,
        /** @var MeasuredPo2Reading[] */
        public readonly array $measuredPo2Readings = [],
        public readonly ?float $noDecoTime = null,
    ) {
        if ($this->depth < 0.0) {
            throw new \InvalidArgumentException('Depth cannot be negative.');
        }

        if ($this->diveTime < 0.0) {
            throw new \InvalidArgumentException('Dive time cannot be negative.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('waypoint');

        foreach ($this->alarms as $alarm) {
            $el->appendChild($alarm->toXml($doc));
        }

        foreach ($this->batteryChargeConditions as $batteryChargeCondition) {
            $el->appendChild($batteryChargeCondition->toXml($doc));
        }

        if ($this->cns !== null) {
            $el->appendChild($doc->createElement('cns', (string) $this->cns));
        }

        foreach ($this->decoStops as $decoStop) {
            $el->appendChild($decoStop->toXml($doc));
        }

        if ($this->bodyTemperature !== null) {
            $el->appendChild($doc->createElement('bodytemperature', (string) $this->bodyTemperature));
        }

        if ($this->calculatedPo2 !== null) {
            $el->appendChild($doc->createElement('calculatedpo2', (string) $this->calculatedPo2));
        }

        $el->appendChild($doc->createElement('depth', (string) $this->depth));
        $el->appendChild($doc->createElement('divetime', (string) $this->diveTime));

        if ($this->heading !== null) {
            $el->appendChild($doc->createElement('heading', (string) $this->heading));
        }

        if ($this->heartRate !== null) {
            $el->appendChild($doc->createElement('heartrate', (string) $this->heartRate));
        }

        if ($this->otu !== null) {
            $el->appendChild($doc->createElement('otu', (string) $this->otu));
        }

        if ($this->pulseRate !== null) {
            $el->appendChild($doc->createElement('pulserate', (string) $this->pulseRate));
        }

        if ($this->remainingBottomTime !== null) {
            $el->appendChild($doc->createElement('remainingbottomtime', (string) $this->remainingBottomTime));
        }

        if ($this->remainingO2Time !== null) {
            $el->appendChild($doc->createElement('remainingo2time', (string) $this->remainingO2Time));
        }

        if ($this->setMarker !== null) {
            $el->appendChild($doc->createElement('setmarker', $this->setMarker));
        }

        if ($this->setPo2 !== null) {
            $el->appendChild($this->setPo2->toXml($doc));
        }

        if ($this->switchMixRef !== null) {
            $switchMix = $doc->createElement('switchmix');
            $switchMix->setAttribute('ref', $this->switchMixRef);
            $el->appendChild($switchMix);
        }

        foreach ($this->tankPressures as $tankPressure) {
            $el->appendChild($tankPressure->toXml($doc));
        }

        if ($this->temperature !== null) {
            $el->appendChild($doc->createElement('temperature', (string) $this->temperature));
        }

        if ($this->diveMode !== null) {
            $divemode = $doc->createElement('divemode');
            $divemode->setAttribute('type', $this->diveMode->value);
            $el->appendChild($divemode);
        }

        if ($this->gradientFactor !== null) {
            $el->appendChild($this->gradientFactor->toXml($doc));
        }

        foreach ($this->measuredPo2Readings as $measuredPo2Reading) {
            $el->appendChild($measuredPo2Reading->toXml($doc));
        }

        if ($this->noDecoTime !== null) {
            $el->appendChild($doc->createElement('nodecotime', (string) $this->noDecoTime));
        }

        return $el;
    }
}
