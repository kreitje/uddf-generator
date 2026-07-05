<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveComputerControl;

use Kreitje\UddfGenerator\XmlSerializable;

final class GetDcData implements XmlSerializable
{
    public function __construct(
        public readonly bool $allData = false,
        public readonly bool $generatorData = false,
        public readonly bool $ownerData = false,
        public readonly bool $buddyData = false,
        public readonly bool $gasDefinitionsData = false,
        public readonly bool $diveSiteData = false,
        public readonly bool $diveTripData = false,
        public readonly bool $profileData = false,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('getdcdata');

        if ($this->allData) {
            $el->appendChild($doc->createElement('getdcalldata'));
        }

        if ($this->generatorData) {
            $el->appendChild($doc->createElement('getdcgeneratordata'));
        }

        if ($this->ownerData) {
            $el->appendChild($doc->createElement('getdcownerdata'));
        }

        if ($this->buddyData) {
            $el->appendChild($doc->createElement('getdcbuddydata'));
        }

        if ($this->gasDefinitionsData) {
            $el->appendChild($doc->createElement('getdcgasdefinitionsdata'));
        }

        if ($this->diveSiteData) {
            $el->appendChild($doc->createElement('getdcdivesitedata'));
        }

        if ($this->diveTripData) {
            $el->appendChild($doc->createElement('getdcdivetripdata'));
        }

        if ($this->profileData) {
            $el->appendChild($doc->createElement('getdcprofiledata'));
        }

        return $el;
    }
}
