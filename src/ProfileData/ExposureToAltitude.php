<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\ProfileData;

use Kreitje\UddfGenerator\Enum\Transportation;
use Kreitje\UddfGenerator\XmlSerializable;

final class ExposureToAltitude implements XmlSerializable
{
    public function __construct(
        public readonly Transportation $transportation,
        public readonly ?int $surfaceIntervalBeforeAltitudeExposure = null,
        public readonly ?\DateTimeImmutable $dateOfFlight = null,
        public readonly ?int $altitudeOfExposure = null,
        public readonly ?int $totalLengthOfExposure = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('exposuretoaltitude');

        if ($this->surfaceIntervalBeforeAltitudeExposure !== null) {
            $el->appendChild($doc->createElement(
                'surfaceintervalbeforealtitudeexposure',
                (string) $this->surfaceIntervalBeforeAltitudeExposure,
            ));
        }

        $el->appendChild($doc->createElement('transportation', $this->transportation->value));

        if ($this->dateOfFlight !== null) {
            $dateOfFlight = $doc->createElement('dateofflight');
            $dateOfFlight->appendChild($doc->createElement('datetime', $this->dateOfFlight->format('Y-m-d\TH:i:s')));
            $el->appendChild($dateOfFlight);
        }

        if ($this->altitudeOfExposure !== null) {
            $el->appendChild($doc->createElement('altitudeofexposure', (string) $this->altitudeOfExposure));
        }

        if ($this->totalLengthOfExposure !== null) {
            $el->appendChild($doc->createElement('totallengthofexposure', (string) $this->totalLengthOfExposure));
        }

        return $el;
    }
}
