<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator;

use Kreitje\UddfGenerator\Diver\Diver;
use Kreitje\UddfGenerator\Diver\Equipment;
use Kreitje\UddfGenerator\Diver\Owner;
use Kreitje\UddfGenerator\Diver\PersonalData;
use Kreitje\UddfGenerator\Diver\Tank;
use Kreitje\UddfGenerator\DiveSite\DiveSite;
use Kreitje\UddfGenerator\DiveSite\Geography;
use Kreitje\UddfGenerator\Gas\GasDefinitions;
use Kreitje\UddfGenerator\Gas\Mix;
use Kreitje\UddfGenerator\Generator\Generator;
use Kreitje\UddfGenerator\Generator\Manufacturer;
use Kreitje\UddfGenerator\ProfileData\Dive;
use Kreitje\UddfGenerator\ProfileData\InformationAfterDive;
use Kreitje\UddfGenerator\ProfileData\InformationBeforeDive;
use Kreitje\UddfGenerator\ProfileData\ProfileData;
use Kreitje\UddfGenerator\ProfileData\RepetitionGroup;
use Kreitje\UddfGenerator\ProfileData\Waypoint;

final class UddfParser
{
    public function parse(string $xml): UddfGenerator
    {
        $prev = libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $loaded = $doc->loadXML($xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        if (!$loaded) {
            $message = isset($errors[0]) ? trim($errors[0]->message) : 'unknown error';
            throw new ParseException("Failed to parse XML: {$message}");
        }

        $root = $doc->documentElement;

        if ($root === null || $root->localName !== 'uddf') {
            throw new ParseException('Root element must be <uddf>.');
        }

        return new UddfGenerator(
            generator: $this->parseGenerator($root),
            diver: $this->parseDiver($root),
            diveSites: $this->parseDiveSites($root),
            gasDefinitions: $this->parseGasDefinitions($root),
            profileData: $this->parseProfileData($root),
        );
    }

    public function parseFile(string $path): Uddf
    {
        if (!is_file($path)) {
            throw new ParseException("File not found: {$path}");
        }

        $xml = file_get_contents($path);

        if ($xml === false) {
            throw new ParseException("Failed to read file: {$path}");
        }

        return $this->parse($xml);
    }

    private function parseGenerator(\DOMElement $root): Generator
    {
        $el = $this->child($root, 'generator');

        if ($el === null) {
            throw new ParseException('Missing required <generator> element.');
        }

        $manufacturerEl = $this->child($el, 'manufacturer');
        $manufacturer = null;

        if ($manufacturerEl !== null) {
            $manufacturer = new Manufacturer(
                id: $manufacturerEl->getAttribute('id'),
                name: $this->require($manufacturerEl, 'name'),
                address: $this->text($manufacturerEl, 'address'),
                phone: $this->text($manufacturerEl, 'phone'),
                email: $this->text($manufacturerEl, 'email'),
            );
        }

        $datetimeStr = $this->text($el, 'datetime');

        return new Generator(
            name: $this->require($el, 'name'),
            version: $this->require($el, 'version'),
            datetime: $datetimeStr !== null ? new \DateTimeImmutable($datetimeStr) : null,
            manufacturer: $manufacturer,
        );
    }

    private function parseDiver(\DOMElement $root): ?Diver
    {
        $diverEl = $this->child($root, 'diver');

        if ($diverEl === null) {
            return null;
        }

        $ownerEl = $this->child($diverEl, 'owner');

        if ($ownerEl === null) {
            return null;
        }

        $personalEl = $this->child($ownerEl, 'personal');
        $birthdateStr = $personalEl !== null ? $this->text($personalEl, 'birthdate') : null;

        $personalData = new PersonalData(
            firstName: $personalEl !== null ? $this->text($personalEl, 'firstname') : null,
            lastName: $personalEl !== null ? $this->text($personalEl, 'lastname') : null,
            birthdate: $birthdateStr !== null ? new \DateTimeImmutable($birthdateStr) : null,
            sex: $personalEl !== null ? $this->text($personalEl, 'sex') : null,
        );

        $equipmentEl = $this->child($ownerEl, 'equipment');
        $equipment = null;

        if ($equipmentEl !== null) {
            $tanks = [];
            foreach ($this->children($equipmentEl, 'tank') as $tankEl) {
                $tanks[] = new Tank(
                    id: $tankEl->getAttribute('id'),
                    name: $this->require($tankEl, 'name'),
                    volume: $this->float($tankEl, 'volume'),
                    workpressure: $this->float($tankEl, 'workpressure'),
                );
            }
            $equipment = new Equipment(tanks: $tanks);
        }

        return new Diver(
            owner: new Owner(
                id: $ownerEl->getAttribute('id') ?: 'owner',
                personalData: $personalData,
                equipment: $equipment,
            ),
        );
    }

    /** @return DiveSite[] */
    private function parseDiveSites(\DOMElement $root): array
    {
        $container = $this->child($root, 'divesite');

        if ($container === null) {
            return [];
        }

        $sites = [];

        foreach ($this->children($container, 'site') as $siteEl) {
            $geoEl = $this->child($siteEl, 'geography');
            $geography = null;

            if ($geoEl !== null) {
                $geography = new Geography(
                    location: $this->text($geoEl, 'location'),
                    latitude: $this->float($geoEl, 'latitude'),
                    longitude: $this->float($geoEl, 'longitude'),
                    country: $this->text($geoEl, 'country'),
                );
            }

            $sites[] = new DiveSite(
                id: $siteEl->getAttribute('id'),
                name: $this->require($siteEl, 'name'),
                geography: $geography,
                notes: $this->text($siteEl, 'notes'),
            );
        }

        return $sites;
    }

    private function parseGasDefinitions(\DOMElement $root): ?GasDefinitions
    {
        $el = $this->child($root, 'gasdefinitions');

        if ($el === null) {
            return null;
        }

        $mixes = [];

        foreach ($this->children($el, 'mix') as $mixEl) {
            $mixes[] = new Mix(
                id: $mixEl->getAttribute('id'),
                name: $this->require($mixEl, 'name'),
                o2: $this->float($mixEl, 'o2') ?? 0.0,
                n2: $this->float($mixEl, 'n2') ?? 0.0,
                he: $this->float($mixEl, 'he') ?? 0.0,
            );
        }

        if ($mixes === []) {
            return null;
        }

        return new GasDefinitions(mixes: $mixes);
    }

    private function parseProfileData(\DOMElement $root): ?ProfileData
    {
        $el = $this->child($root, 'profiledata');

        if ($el === null) {
            return null;
        }

        $groups = [];

        foreach ($this->children($el, 'repetitiongroup') as $groupEl) {
            $dives = [];

            foreach ($this->children($groupEl, 'dive') as $diveEl) {
                $dives[] = $this->parseDive($diveEl);
            }

            if ($dives !== []) {
                $groups[] = new RepetitionGroup(
                    id: $groupEl->getAttribute('id'),
                    dives: $dives,
                );
            }
        }

        if ($groups === []) {
            return null;
        }

        return new ProfileData(repetitionGroups: $groups);
    }

    private function parseDive(\DOMElement $diveEl): Dive
    {
        $id = $diveEl->getAttribute('id');
        $beforeEl = $this->child($diveEl, 'informationbeforedive');

        if ($beforeEl === null) {
            throw new ParseException("Missing <informationbeforedive> in <dive id=\"{$id}\">.");
        }

        $linkEl = $this->child($beforeEl, 'link');

        $informationBefore = new InformationBeforeDive(
            datetime: new \DateTimeImmutable($this->require($beforeEl, 'datetime')),
            diveNumber: $this->int($beforeEl, 'divenumber'),
            diveSiteRef: ($linkEl?->getAttribute('ref') ?: null),
            notes: $this->text($beforeEl, 'notes'),
        );

        $samplesEl = $this->child($diveEl, 'samples');
        $waypoints = [];

        if ($samplesEl !== null) {
            foreach ($this->children($samplesEl, 'waypoint') as $wpEl) {
                $mixChangeEl = $this->child($wpEl, 'mixchange');

                $waypoints[] = new Waypoint(
                    depth: $this->float($wpEl, 'depth') ?? 0.0,
                    diveTime: $this->int($wpEl, 'divetime') ?? 0,
                    temperature: $this->float($wpEl, 'temperature'),
                    tankPressure: $this->float($wpEl, 'tankpressure'),
                    mixChangeRef: ($mixChangeEl?->getAttribute('ref') ?: null),
                );
            }
        }

        $afterEl = $this->child($diveEl, 'informationafterdive');
        $informationAfter = null;

        if ($afterEl !== null) {
            $informationAfter = new InformationAfterDive(
                greatestDepth: $this->float($afterEl, 'greatestdepth') ?? 0.0,
                diveDuration: $this->int($afterEl, 'diveduration') ?? 0,
                averageDepth: $this->float($afterEl, 'averagedepth'),
                notes: $this->text($afterEl, 'notes'),
            );
        }

        return new Dive(
            id: $id,
            informationBeforeDive: $informationBefore,
            samples: $waypoints,
            informationAfterDive: $informationAfter,
        );
    }

    private function child(\DOMElement $parent, string $localName): ?\DOMElement
    {
        for ($node = $parent->firstChild; $node !== null; $node = $node->nextSibling) {
            if ($node instanceof \DOMElement && $node->localName === $localName) {
                return $node;
            }
        }

        return null;
    }

    /** @return \DOMElement[] */
    private function children(\DOMElement $parent, string $localName): array
    {
        $result = [];

        for ($node = $parent->firstChild; $node !== null; $node = $node->nextSibling) {
            if ($node instanceof \DOMElement && $node->localName === $localName) {
                $result[] = $node;
            }
        }

        return $result;
    }

    private function text(\DOMElement $parent, string $localName): ?string
    {
        $el = $this->child($parent, $localName);

        if ($el === null) {
            return null;
        }

        $value = trim($el->textContent);

        return $value !== '' ? $value : null;
    }

    private function require(\DOMElement $parent, string $localName): string
    {
        $value = $this->text($parent, $localName);

        if ($value === null) {
            throw new ParseException("Missing required element <{$localName}> inside <{$parent->localName}>.");
        }

        return $value;
    }

    private function float(\DOMElement $parent, string $localName): ?float
    {
        $value = $this->text($parent, $localName);

        return $value !== null ? (float) $value : null;
    }

    private function int(\DOMElement $parent, string $localName): ?int
    {
        $value = $this->text($parent, $localName);

        return $value !== null ? (int) $value : null;
    }
}
