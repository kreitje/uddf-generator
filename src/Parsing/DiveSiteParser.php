<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Parsing;

use Kreitje\UddfGenerator\DiveSite\Cave;
use Kreitje\UddfGenerator\DiveSite\Divebase;
use Kreitje\UddfGenerator\DiveSite\DiveSite;
use Kreitje\UddfGenerator\DiveSite\Ecology;
use Kreitje\UddfGenerator\DiveSite\Geography;
use Kreitje\UddfGenerator\DiveSite\Guide;
use Kreitje\UddfGenerator\DiveSite\Indoor;
use Kreitje\UddfGenerator\DiveSite\Place;
use Kreitje\UddfGenerator\DiveSite\SiteData;
use Kreitje\UddfGenerator\DiveSite\Wreck;
use Kreitje\UddfGenerator\Enum\Environment;
use Kreitje\UddfGenerator\Enum\GlobalLightIntensity;
use Kreitje\UddfGenerator\Common\PricePerDivePackage;

final class DiveSiteParser
{
    use DomHelpers;

    /** @return DiveSite[] */
    public function parseSites(\DOMElement $root): array
    {
        $container = $this->child($root, 'divesite');

        if ($container === null) {
            return [];
        }

        $sites = [];

        foreach ($this->children($container, 'site') as $siteEl) {
            $geoEl = $this->child($siteEl, 'geography');

            $sites[] = new DiveSite(
                id: $siteEl->getAttribute('id'),
                name: $this->require($siteEl, 'name'),
                aliasNames: $this->texts($siteEl, 'aliasname'),
                environment: $this->parseEnum(Environment::class, $siteEl, 'environment'),
                geography: $geoEl !== null ? new Geography(
                    location: $this->require($geoEl, 'location'),
                    address: $this->parseAddress($geoEl),
                    latitude: $this->float($geoEl, 'latitude'),
                    longitude: $this->float($geoEl, 'longitude'),
                    altitude: $this->float($geoEl, 'altitude'),
                    timezone: $this->float($geoEl, 'timezone'),
                ) : null,
                ecology: $this->parseEcology($siteEl),
                siteData: $this->parseSiteData($siteEl),
                ratings: $this->parseRatings($siteEl),
                notes: $this->parseNotes($siteEl),
            );
        }

        return $sites;
    }

    /** @return Divebase[] */
    public function parseDiveBases(\DOMElement $root): array
    {
        $container = $this->child($root, 'divesite');

        if ($container === null) {
            return [];
        }

        $bases = [];

        foreach ($this->children($container, 'divebase') as $el) {
            $linkEl = $this->child($el, 'link');
            $priceDivePackageEl = $this->child($el, 'pricedivepackage');

            $bases[] = new Divebase(
                id: $el->getAttribute('id'),
                name: $this->require($el, 'name'),
                aliasNames: $this->texts($el, 'aliasname'),
                address: $this->parseAddress($el),
                contact: $this->parseContact($el),
                pricePerDive: $this->parsePrice($el, 'priceperdive'),
                priceDivePackage: $priceDivePackageEl !== null ? new PricePerDivePackage(
                    amount: (float) trim($priceDivePackageEl->textContent),
                    currency: $this->attr($priceDivePackageEl, 'currency'),
                    noOfDives: $this->attr($priceDivePackageEl, 'noofdives'),
                ) : null,
                guides: array_map(
                    fn (\DOMElement $guideEl): Guide => $this->parseGuide($guideEl),
                    $this->children($el, 'guide'),
                ),
                ratings: $this->parseRatings($el),
                linkRef: $linkEl?->getAttribute('ref'),
                notes: $this->parseNotes($el),
            );
        }

        return $bases;
    }

    private function parseGuide(\DOMElement $el): Guide
    {
        $linkEl = $this->child($el, 'link');

        return new Guide(
            id: $el->getAttribute('id'),
            linkRef: $linkEl?->getAttribute('ref') ?? '',
        );
    }

    private function parseEcology(\DOMElement $parent): ?Ecology
    {
        $el = $this->child($parent, 'ecology');

        if ($el === null) {
            return null;
        }

        return new Ecology(
            fauna: $this->parseFauna($el),
            flora: $this->parseFlora($el),
        );
    }

    private function parseSiteData(\DOMElement $parent): ?SiteData
    {
        $el = $this->child($parent, 'sitedata');

        if ($el === null) {
            return null;
        }

        $lakeEl = $this->child($el, 'lake');
        $riverEl = $this->child($el, 'river');
        $shoreEl = $this->child($el, 'shore');
        $caveEl = $this->child($el, 'cave');
        $indoorEl = $this->child($el, 'indoor');

        return new SiteData(
            areaLength: $this->float($el, 'arealength'),
            areaWidth: $this->float($el, 'areawidth'),
            averageVisibility: $this->float($el, 'averagevisibility'),
            bottom: $this->text($el, 'bottom'),
            density: $this->float($el, 'density'),
            difficulty: $this->int($el, 'difficulty'),
            globalLightIntensity: $this->parseEnum(GlobalLightIntensity::class, $el, 'globallightintensity'),
            maximumDepth: $this->float($el, 'maximumdepth'),
            maximumVisibility: $this->float($el, 'maximumvisibility'),
            minimumDepth: $this->float($el, 'minimumdepth'),
            minimumVisibility: $this->float($el, 'minimumvisibility'),
            terrain: $this->text($el, 'terrain'),
            wrecks: array_map(
                fn (\DOMElement $wreckEl): Wreck => $this->parseWreck($wreckEl),
                $this->children($el, 'wreck'),
            ),
            cave: $caveEl !== null ? new Cave(
                id: $caveEl->getAttribute('id'),
                name: $this->require($caveEl, 'name'),
                aliasNames: $this->texts($caveEl, 'aliasname'),
                notes: $this->parseNotes($caveEl),
            ) : null,
            indoor: $indoorEl !== null ? new Indoor(
                name: $this->require($indoorEl, 'name'),
                aliasNames: $this->texts($indoorEl, 'aliasname'),
                address: $this->parseAddress($indoorEl),
                contact: $this->parseContact($indoorEl),
                notes: $this->parseNotes($indoorEl),
            ) : null,
            lake: $lakeEl !== null ? $this->parsePlace($lakeEl) : null,
            river: $riverEl !== null ? $this->parsePlace($riverEl) : null,
            shore: $shoreEl !== null ? $this->parsePlace($shoreEl) : null,
        );
    }

    private function parsePlace(\DOMElement $el): Place
    {
        return new Place(
            name: $this->require($el, 'name'),
            aliasNames: $this->texts($el, 'aliasname'),
            notes: $this->parseNotes($el),
        );
    }

    private function parseWreck(\DOMElement $el): Wreck
    {
        $builtEl = $this->child($el, 'built');
        $sunkStr = $this->parseEncapsulatedDateTime($el, 'sunk');

        return new Wreck(
            name: $this->require($el, 'name'),
            aliasNames: $this->texts($el, 'aliasname'),
            shipType: $this->text($el, 'shiptype'),
            nationality: $this->text($el, 'nationality'),
            shipyard: $builtEl !== null ? $this->text($builtEl, 'shipyard') : null,
            launchingDate: $builtEl !== null ? $this->parseEncapsulatedDateTime($builtEl, 'launchingdate') : null,
            shipDimension: $this->parseDimension($el),
            sunk: $sunkStr,
            notes: $this->parseNotes($el),
        );
    }
}
