<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\TableGeneration;

use Kreitje\UddfGenerator\Spec\V323\ProfileData\ApplicationData;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\InputProfile;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\SurfaceInterval;
use Kreitje\UddfGenerator\XmlSerializable;

final class BaseCalculation implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $title = null,
        /** @var string[] */
        public readonly array $linkRefs = [],
        public readonly ?SurfaceInterval $surfaceIntervalAfterDive = null,
        public readonly ?SurfaceInterval $surfaceIntervalBeforeDive = null,
        public readonly ?float $density = null,
        public readonly ?float $maximumAscendingRate = null,
        public readonly ?Output $output = null,
        public readonly ?ApplicationData $applicationData = null,
        public readonly ?string $decoModel = null,
        public readonly ?float $deepStopTime = null,
        public readonly ?MixChange $mixChange = null,
        public readonly ?InputProfile $inputProfile = null,
    ) {}

    public function toXml(\DOMDocument $doc, string $elementName = 'profile'): \DOMElement
    {
        $el = $doc->createElement($elementName);
        $el->setAttribute('id', $this->id);

        if ($this->title !== null) {
            $el->appendChild($doc->createElement('title', $this->title));
        }

        foreach ($this->linkRefs as $linkRef) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $linkRef);
            $el->appendChild($link);
        }

        if ($this->surfaceIntervalAfterDive !== null) {
            $el->appendChild($this->surfaceIntervalAfterDive->toXml($doc, 'surfaceintervalafterdive'));
        }

        if ($this->surfaceIntervalBeforeDive !== null) {
            $el->appendChild($this->surfaceIntervalBeforeDive->toXml($doc, 'surfaceintervalbeforedive'));
        }

        if ($this->density !== null) {
            $el->appendChild($doc->createElement('density', (string) $this->density));
        }

        if ($this->maximumAscendingRate !== null) {
            $el->appendChild($doc->createElement('maximumascendingrate', (string) $this->maximumAscendingRate));
        }

        if ($this->output !== null) {
            $el->appendChild($this->output->toXml($doc));
        }

        if ($this->applicationData !== null) {
            $el->appendChild($this->applicationData->toXml($doc));
        }

        if ($this->decoModel !== null) {
            $el->appendChild($doc->createElement('decomodel', $this->decoModel));
        }

        if ($this->deepStopTime !== null) {
            $el->appendChild($doc->createElement('deepstoptime', (string) $this->deepStopTime));
        }

        if ($this->mixChange !== null) {
            $el->appendChild($this->mixChange->toXml($doc));
        }

        if ($this->inputProfile !== null) {
            $el->appendChild($this->inputProfile->toXml($doc));
        }

        return $el;
    }
}
