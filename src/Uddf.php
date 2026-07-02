<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator;

use Kreitje\UddfGenerator\Diver\Diver;
use Kreitje\UddfGenerator\DiveSite\DiveSite;
use Kreitje\UddfGenerator\Gas\GasDefinitions;
use Kreitje\UddfGenerator\Generator\Generator;
use Kreitje\UddfGenerator\ProfileData\ProfileData;

final class Uddf implements XmlSerializable
{
    public const VERSION = '3.2.3';

    public function __construct(
        public readonly Generator $generator,
        public readonly ?Diver $diver = null,
        /** @var DiveSite[] */
        public readonly array $diveSites = [],
        public readonly ?GasDefinitions $gasDefinitions = null,
        public readonly ?ProfileData $profileData = null,
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

        if ($this->diver !== null) {
            $root->appendChild($this->diver->toXml($doc));
        }

        if ($this->diveSites !== []) {
            $diveSiteEl = $doc->createElement('divesite');
            foreach ($this->diveSites as $site) {
                $diveSiteEl->appendChild($site->toXml($doc));
            }
            $root->appendChild($diveSiteEl);
        }

        if ($this->gasDefinitions !== null) {
            $root->appendChild($this->gasDefinitions->toXml($doc));
        }

        if ($this->profileData !== null) {
            $root->appendChild($this->profileData->toXml($doc));
        }

        return $root;
    }
}
