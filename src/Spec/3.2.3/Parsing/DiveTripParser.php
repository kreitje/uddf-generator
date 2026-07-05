<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Parsing;

use Kreitje\UddfGenerator\Spec\V323\Common\PricePerDivePackage;
use Kreitje\UddfGenerator\Spec\V323\DiveSite\Geography;
use Kreitje\UddfGenerator\Spec\V323\DiveTrip\Accommodation;
use Kreitje\UddfGenerator\Spec\V323\DiveTrip\DateOfTrip;
use Kreitje\UddfGenerator\Spec\V323\DiveTrip\DiveTrip;
use Kreitje\UddfGenerator\Spec\V323\DiveTrip\Operator;
use Kreitje\UddfGenerator\Spec\V323\DiveTrip\Trip;
use Kreitje\UddfGenerator\Spec\V323\DiveTrip\TripPart;
use Kreitje\UddfGenerator\Spec\V323\DiveTrip\Vessel;

final class DiveTripParser
{
    use DomHelpers;

    public function parse(\DOMElement $root): ?DiveTrip
    {
        $el = $this->child($root, 'divetrip');

        if ($el === null) {
            return null;
        }

        $trips = array_map(fn (\DOMElement $e): Trip => $this->parseTrip($e), $this->children($el, 'trip'));

        if ($trips === []) {
            return null;
        }

        return new DiveTrip(trips: $trips);
    }

    private function parseTrip(\DOMElement $el): Trip
    {
        return new Trip(
            id: $el->getAttribute('id'),
            name: $this->require($el, 'name'),
            aliasNames: $this->texts($el, 'aliasname'),
            ratings: $this->parseRatings($el),
            tripParts: array_map(fn (\DOMElement $e): TripPart => $this->parseTripPart($e), $this->children($el, 'trippart')),
        );
    }

    private function parseTripPart(\DOMElement $el): TripPart
    {
        $dateOfTripEl = $this->child($el, 'dateoftrip');
        $geographyEl = $this->child($el, 'geography');
        $accommodationEl = $this->child($el, 'accomodation');
        $operatorEl = $this->child($el, 'operator');
        $vesselEl = $this->child($el, 'vessel');
        $linkEl = $this->child($el, 'link');
        $relatedDivesEl = $this->child($el, 'relateddives');

        return new TripPart(
            name: $this->require($el, 'name'),
            aliasNames: $this->texts($el, 'aliasname'),
            type: $this->attr($el, 'type'),
            dateOfTrip: $dateOfTripEl !== null ? new DateOfTrip(
                startDate: new \DateTimeImmutable($dateOfTripEl->getAttribute('startdate')),
                endDate: new \DateTimeImmutable($dateOfTripEl->getAttribute('enddate')),
            ) : null,
            geography: $geographyEl !== null ? new Geography(
                location: $this->require($geographyEl, 'location'),
                address: $this->parseAddress($geographyEl),
                latitude: $this->float($geographyEl, 'latitude'),
                longitude: $this->float($geographyEl, 'longitude'),
                altitude: $this->float($geographyEl, 'altitude'),
                timezone: $this->float($geographyEl, 'timezone'),
            ) : null,
            accommodation: $accommodationEl !== null ? new Accommodation(
                id: $accommodationEl->getAttribute('id'),
                name: $this->require($accommodationEl, 'name'),
                aliasNames: $this->texts($accommodationEl, 'aliasname'),
                category: $this->text($accommodationEl, 'category'),
                address: $this->parseAddress($accommodationEl),
                contact: $this->parseContact($accommodationEl),
                ratings: $this->parseRatings($accommodationEl),
                notes: $this->parseNotes($accommodationEl),
            ) : null,
            operator: $operatorEl !== null ? new Operator(
                name: $this->require($operatorEl, 'name'),
                aliasNames: $this->texts($operatorEl, 'aliasname'),
                address: $this->parseAddress($operatorEl),
                contact: $this->parseContact($operatorEl),
                ratings: $this->parseRatings($operatorEl),
                notes: $this->parseNotes($operatorEl),
            ) : null,
            vessel: $vesselEl !== null ? new Vessel(
                id: $vesselEl->getAttribute('id'),
                name: $this->require($vesselEl, 'name'),
                aliasNames: $this->texts($vesselEl, 'aliasname'),
                shipType: $this->text($vesselEl, 'shiptype'),
                marina: $this->text($vesselEl, 'marina'),
                address: $this->parseAddress($vesselEl),
                contact: $this->parseContact($vesselEl),
                shipDimension: $this->parseDimension($vesselEl),
                ratings: $this->parseRatings($vesselEl),
                notes: $this->parseNotes($vesselEl),
            ) : null,
            linkRef: $linkEl?->getAttribute('ref'),
            relatedDiveRefs: $relatedDivesEl !== null ? array_map(
                static fn (\DOMElement $link): string => $link->getAttribute('ref'),
                $this->children($relatedDivesEl, 'link'),
            ) : [],
            priceDivePackage: ($p = $this->child($el, 'pricedivepackage')) !== null ? new PricePerDivePackage(
                amount: (float) trim($p->textContent),
                currency: $this->attr($p, 'currency'),
                noOfDives: $this->attr($p, 'noofdives'),
            ) : null,
            pricePerDive: $this->parsePrice($el, 'priceperdive'),
            ratings: $this->parseRatings($el),
            notes: $this->parseNotes($el),
        );
    }
}
