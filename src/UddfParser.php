<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator;

use Kreitje\UddfGenerator\Common\Address;
use Kreitje\UddfGenerator\Common\Contact;
use Kreitje\UddfGenerator\Common\Notes;
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

        $diveSites = $this->parseDiveSites($root);

        return new UddfGenerator(
            generator: $this->parseGenerator($root),
            diver: $this->parseDiver($root),
            diveSites: $diveSites,
            gasDefinitions: $this->parseGasDefinitions($root),
            profileData: $this->parseProfileData($root, array_map(
                static fn (DiveSite $site): string => $site->id,
                $diveSites,
            )),
        );
    }

    public function parseFile(string $path): UddfGenerator
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
                address: $this->parseAddress($manufacturerEl),
                contact: $this->parseContact($manufacturerEl),
            );
        }

        $datetimeStr = $this->text($el, 'datetime');

        return new Generator(
            name: $this->require($el, 'name'),
            manufacturer: $manufacturer,
            version: $this->text($el, 'version'),
            datetime: $datetimeStr !== null ? new \DateTimeImmutable($datetimeStr) : null,
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

        if ($personalEl === null) {
            throw new ParseException('Missing required <personal> element inside <owner>.');
        }

        $personalData = new PersonalData(
            firstName: $this->require($personalEl, 'firstname'),
            lastName: $this->require($personalEl, 'lastname'),
            birthdate: $this->parseEncapsulatedDateTime($personalEl, 'birthdate'),
            sex: $this->text($personalEl, 'sex'),
        );

        $equipmentEl = $this->child($ownerEl, 'equipment');
        $equipment = null;

        if ($equipmentEl !== null) {
            $tanks = [];
            foreach ($this->children($equipmentEl, 'tank') as $tankEl) {
                $tanks[] = new Tank(
                    id: $tankEl->getAttribute('id'),
                    name: $this->require($tankEl, 'name'),
                    volume: $this->float($tankEl, 'tankvolume'),
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
                    location: $this->require($geoEl, 'location'),
                    address: $this->parseAddress($geoEl),
                    latitude: $this->float($geoEl, 'latitude'),
                    longitude: $this->float($geoEl, 'longitude'),
                );
            }

            $sites[] = new DiveSite(
                id: $siteEl->getAttribute('id'),
                name: $this->require($siteEl, 'name'),
                geography: $geography,
                notes: $this->parseNotes($siteEl),
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

    /** @param string[] $knownDiveSiteIds */
    private function parseProfileData(\DOMElement $root, array $knownDiveSiteIds): ?ProfileData
    {
        $el = $this->child($root, 'profiledata');

        if ($el === null) {
            return null;
        }

        $groups = [];

        foreach ($this->children($el, 'repetitiongroup') as $groupEl) {
            $dives = [];

            foreach ($this->children($groupEl, 'dive') as $diveEl) {
                $dives[] = $this->parseDive($diveEl, $knownDiveSiteIds);
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

    /** @param string[] $knownDiveSiteIds */
    private function parseDive(\DOMElement $diveEl, array $knownDiveSiteIds): Dive
    {
        $id = $diveEl->getAttribute('id');
        $beforeEl = $this->child($diveEl, 'informationbeforedive');

        if ($beforeEl === null) {
            throw new ParseException("Missing <informationbeforedive> in <dive id=\"{$id}\">.");
        }

        $informationBefore = new InformationBeforeDive(
            datetime: new \DateTimeImmutable($this->require($beforeEl, 'datetime')),
            diveNumber: $this->int($beforeEl, 'divenumber'),
            diveSiteRef: $this->findDiveSiteLinkRef($beforeEl, $knownDiveSiteIds),
        );

        $samplesEl = $this->child($diveEl, 'samples');
        $waypoints = [];

        if ($samplesEl !== null) {
            foreach ($this->children($samplesEl, 'waypoint') as $wpEl) {
                $switchMixEl = $this->child($wpEl, 'switchmix');

                $waypoints[] = new Waypoint(
                    depth: $this->float($wpEl, 'depth') ?? 0.0,
                    diveTime: $this->float($wpEl, 'divetime') ?? 0.0,
                    temperature: $this->float($wpEl, 'temperature'),
                    tankPressure: $this->float($wpEl, 'tankpressure'),
                    switchMixRef: ($switchMixEl?->getAttribute('ref') ?: null),
                );
            }
        }

        $afterEl = $this->child($diveEl, 'informationafterdive');
        $informationAfter = null;

        if ($afterEl !== null) {
            $informationAfter = new InformationAfterDive(
                greatestDepth: $this->float($afterEl, 'greatestdepth') ?? 0.0,
                diveDuration: $this->float($afterEl, 'diveduration') ?? 0.0,
                averageDepth: $this->float($afterEl, 'averagedepth'),
                notes: $this->parseNotes($afterEl),
            );
        }

        return new Dive(
            id: $id,
            informationBeforeDive: $informationBefore,
            samples: $waypoints,
            informationAfterDive: $informationAfter,
        );
    }

    /**
     * <informationbeforedive> may contain several <link> elements pointing at
     * unrelated things (buddy, trip membership, equipment used, the dive
     * site, ...) — the schema does not distinguish between them structurally.
     * Prefer a link whose ref matches a known dive site id; if none match and
     * there is exactly one link, assume it is the dive site reference (this
     * is always true for documents this library itself generates).
     *
     * @param string[] $knownDiveSiteIds
     */
    private function findDiveSiteLinkRef(\DOMElement $beforeEl, array $knownDiveSiteIds): ?string
    {
        $links = $this->children($beforeEl, 'link');

        foreach ($links as $link) {
            $ref = $link->getAttribute('ref');
            if (in_array($ref, $knownDiveSiteIds, true)) {
                return $ref;
            }
        }

        if (count($links) === 1) {
            return $links[0]->getAttribute('ref') ?: null;
        }

        return null;
    }

    private function parseAddress(\DOMElement $parent): ?Address
    {
        $el = $this->child($parent, 'address');

        if ($el === null) {
            return null;
        }

        return new Address(
            country: $this->require($el, 'country'),
            street: $this->text($el, 'street'),
            city: $this->text($el, 'city'),
            postcode: $this->text($el, 'postcode'),
            province: $this->text($el, 'province'),
        );
    }

    private function parseContact(\DOMElement $parent): ?Contact
    {
        $el = $this->child($parent, 'contact');

        if ($el === null) {
            return null;
        }

        return new Contact(
            languages: $this->texts($el, 'language'),
            phones: $this->texts($el, 'phone'),
            mobilePhones: $this->texts($el, 'mobilephone'),
            faxes: $this->texts($el, 'fax'),
            emails: $this->texts($el, 'email'),
            homepages: $this->texts($el, 'homepage'),
        );
    }

    private function parseNotes(\DOMElement $parent): ?Notes
    {
        $el = $this->child($parent, 'notes');

        if ($el === null) {
            return null;
        }

        $paragraphs = [];
        $linkRefs = [];

        for ($node = $el->firstChild; $node !== null; $node = $node->nextSibling) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            if ($node->localName === 'para') {
                $paragraphs[] = trim($node->textContent);
            } elseif ($node->localName === 'link') {
                $linkRefs[] = $node->getAttribute('ref');
            }
        }

        if ($paragraphs === [] && $linkRefs === []) {
            return null;
        }

        return new Notes(paragraphs: $paragraphs, linkRefs: $linkRefs);
    }

    private function parseEncapsulatedDateTime(\DOMElement $parent, string $localName): ?\DateTimeImmutable
    {
        $wrapper = $this->child($parent, $localName);

        if ($wrapper === null) {
            return null;
        }

        $value = $this->text($wrapper, 'datetime');

        return $value !== null ? new \DateTimeImmutable($value) : null;
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

    /** @return string[] */
    private function texts(\DOMElement $parent, string $localName): array
    {
        return array_map(
            static fn (\DOMElement $el): string => trim($el->textContent),
            $this->children($parent, $localName),
        );
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
        $el = $this->child($parent, $localName);

        if ($el === null) {
            throw new ParseException("Missing required element <{$localName}> inside <{$parent->localName}>.");
        }

        return trim($el->textContent);
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
