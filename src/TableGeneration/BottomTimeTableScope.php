<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\TableGeneration;

use Kreitje\UddfGenerator\XmlSerializable;

final class BottomTimeTableScope implements XmlSerializable
{
    public function __construct(
        public readonly float $diveDepthBegin,
        public readonly float $diveDepthEnd,
        public readonly float $diveDepthStep,
        public readonly float $breathingConsumptionVolumeBegin,
        public readonly float $breathingConsumptionVolumeEnd,
        public readonly float $breathingConsumptionVolumeStep,
        public readonly float $tankVolumeBegin,
        public readonly float $tankVolumeEnd,
        public readonly float $tankVolumeStep,
        public readonly float $tankPressureBegin,
        public readonly float $tankPressureReserve,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('bottomtimetablescope');

        $el->appendChild($doc->createElement('divedepthbegin', (string) $this->diveDepthBegin));
        $el->appendChild($doc->createElement('divedepthend', (string) $this->diveDepthEnd));
        $el->appendChild($doc->createElement('divedepthstep', (string) $this->diveDepthStep));
        $el->appendChild($doc->createElement('breathingconsumptionvolumebegin', (string) $this->breathingConsumptionVolumeBegin));
        $el->appendChild($doc->createElement('breathingconsumptionvolumeend', (string) $this->breathingConsumptionVolumeEnd));
        $el->appendChild($doc->createElement('breathingconsumptionvolumestep', (string) $this->breathingConsumptionVolumeStep));
        $el->appendChild($doc->createElement('tankvolumebegin', (string) $this->tankVolumeBegin));
        $el->appendChild($doc->createElement('tankvolumeend', (string) $this->tankVolumeEnd));
        $el->appendChild($doc->createElement('tankvolumestep', (string) $this->tankVolumeStep));
        $el->appendChild($doc->createElement('tankpressurebegin', (string) $this->tankPressureBegin));
        $el->appendChild($doc->createElement('tankpressurereserve', (string) $this->tankPressureReserve));

        return $el;
    }
}
