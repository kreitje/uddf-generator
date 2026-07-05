<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\TableGeneration;

use Kreitje\UddfGenerator\Spec\V323\ProfileData\ApplicationData;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\InputProfile;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\SurfaceInterval;
use Kreitje\UddfGenerator\XmlSerializable;

final class Table implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly TableScope $tableScope,
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

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $base = new BaseCalculation(
            id: $this->id,
            title: $this->title,
            linkRefs: $this->linkRefs,
            surfaceIntervalAfterDive: $this->surfaceIntervalAfterDive,
            surfaceIntervalBeforeDive: $this->surfaceIntervalBeforeDive,
            density: $this->density,
            maximumAscendingRate: $this->maximumAscendingRate,
            output: $this->output,
            applicationData: $this->applicationData,
            decoModel: $this->decoModel,
            deepStopTime: $this->deepStopTime,
            mixChange: $this->mixChange,
            inputProfile: $this->inputProfile,
        );

        $el = $base->toXml($doc, 'table');
        $el->appendChild($this->tableScope->toXml($doc));

        return $el;
    }
}
