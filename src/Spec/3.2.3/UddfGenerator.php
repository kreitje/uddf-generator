<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323;

use Kreitje\UddfGenerator\Spec\IUDDFGeneratorVersion;
use Kreitje\UddfGenerator\XmlSerializable;
use Kreitje\UddfGenerator\Spec\V323\Business\Business;
use Kreitje\UddfGenerator\Spec\V323\DecoModel\DecoModel;
use Kreitje\UddfGenerator\Spec\V323\DiveComputerControl\DiveComputerControl;
use Kreitje\UddfGenerator\Spec\V323\Diver\Diver;
use Kreitje\UddfGenerator\Spec\V323\DiveSite\DiveSite;
use Kreitje\UddfGenerator\Spec\V323\DiveTrip\DiveTrip;
use Kreitje\UddfGenerator\Spec\V323\Gas\GasDefinitions;
use Kreitje\UddfGenerator\Spec\V323\Generator\Generator;
use Kreitje\UddfGenerator\Spec\V323\Maker\Maker;
use Kreitje\UddfGenerator\Spec\V323\Media\MediaData;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\ProfileData;
use Kreitje\UddfGenerator\Spec\V323\TableGeneration\TableGeneration;

final class UddfGenerator implements XmlSerializable, IUDDFGeneratorVersion
{
    public const VERSION = '3.2.3';

    public function __construct(
        public readonly Generator $generator,
        public readonly ?MediaData $media = null,
        public readonly ?Maker $maker = null,
        public readonly ?Business $business = null,
        public readonly ?Diver $diver = null,
        /** @var DiveSite[] */
        public readonly array $diveSites = [],
        /** @var \Kreitje\UddfGenerator\Spec\V323\DiveSite\Divebase[] */
        public readonly array $diveBases = [],
        public readonly ?DiveTrip $diveTrip = null,
        public readonly ?GasDefinitions $gasDefinitions = null,
        public readonly ?DecoModel $decoModel = null,
        public readonly ?ProfileData $profileData = null,
        public readonly ?TableGeneration $tableGeneration = null,
        public readonly ?DiveComputerControl $diveComputerControl = null,
    ) {}

    public function generate(): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        $doc->appendChild($this->toXml($doc));

        $xml = $doc->saveXML();

        if ($xml === false) {
            throw new \RuntimeException('Failed to generate XML.');
        }

        return $xml;
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $root = $doc->createElement('uddf');
        $root->setAttribute('version', self::VERSION);
        $root->setAttribute('xmlns', 'http://www.streit.cc/uddf/3.2/');

        $root->appendChild($this->generator->toXml($doc));

        if ($this->media !== null) {
            $root->appendChild($this->media->toXml($doc));
        }

        if ($this->maker !== null) {
            $root->appendChild($this->maker->toXml($doc));
        }

        if ($this->business !== null) {
            $root->appendChild($this->business->toXml($doc));
        }

        if ($this->diver !== null) {
            $root->appendChild($this->diver->toXml($doc));
        }

        if ($this->diveSites !== [] || $this->diveBases !== []) {
            $diveSiteEl = $doc->createElement('divesite');
            foreach ($this->diveBases as $divebase) {
                $diveSiteEl->appendChild($divebase->toXml($doc));
            }
            foreach ($this->diveSites as $site) {
                $diveSiteEl->appendChild($site->toXml($doc));
            }
            $root->appendChild($diveSiteEl);
        }

        if ($this->diveTrip !== null) {
            $root->appendChild($this->diveTrip->toXml($doc));
        }

        if ($this->gasDefinitions !== null) {
            $root->appendChild($this->gasDefinitions->toXml($doc));
        }

        if ($this->decoModel !== null) {
            $root->appendChild($this->decoModel->toXml($doc));
        }

        if ($this->profileData !== null) {
            $root->appendChild($this->profileData->toXml($doc));
        }

        if ($this->tableGeneration !== null) {
            $root->appendChild($this->tableGeneration->toXml($doc));
        }

        if ($this->diveComputerControl !== null) {
            $root->appendChild($this->diveComputerControl->toXml($doc));
        }

        return $root;
    }

    public function getVersion(): string
    {
        return self::VERSION;
    }
}
