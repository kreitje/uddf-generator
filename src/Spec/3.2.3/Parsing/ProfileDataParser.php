<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Parsing;

use Kreitje\UddfGenerator\Spec\V323\Common\Notes;
use Kreitje\UddfGenerator\Spec\V323\Common\Price;
use Kreitje\UddfGenerator\Spec\V323\Enum\Apparatus;
use Kreitje\UddfGenerator\Spec\V323\Enum\Current;
use Kreitje\UddfGenerator\Spec\V323\Enum\DivePlan;
use Kreitje\UddfGenerator\Spec\V323\Enum\DiveTable;
use Kreitje\UddfGenerator\Spec\V323\Enum\EquipmentMalfunction;
use Kreitje\UddfGenerator\Spec\V323\Enum\GlobalAlarm;
use Kreitje\UddfGenerator\Spec\V323\Enum\Platform;
use Kreitje\UddfGenerator\Spec\V323\Enum\Problems;
use Kreitje\UddfGenerator\Spec\V323\Enum\Program;
use Kreitje\UddfGenerator\Spec\V323\Enum\Purpose;
use Kreitje\UddfGenerator\Spec\V323\Enum\StateOfRestBeforeDive;
use Kreitje\UddfGenerator\Spec\V323\Enum\ThermalComfort;
use Kreitje\UddfGenerator\Spec\V323\Enum\Workload;
use Kreitje\UddfGenerator\ParseException;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\Dive;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\Drug;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\EquipmentUsed;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\InformationAfterDive;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\InformationBeforeDive;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\InputProfile;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\Observations;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\ProfileData;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\RepetitionGroup;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\TankData;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\Waypoint;

final class ProfileDataParser
{
    use DomHelpers;

    /**
     * @param string[] $knownDiveSiteIds
     * @param string[] $knownTankIds
     * @param string[] $knownMixIds
     */
    public function parse(
        \DOMElement $root,
        array $knownDiveSiteIds,
        array $knownTankIds,
        array $knownMixIds,
    ): ?ProfileData {
        $el = $this->child($root, 'profiledata');

        if ($el === null) {
            return null;
        }

        $groups = [];

        foreach ($this->children($el, 'repetitiongroup') as $groupEl) {
            $dives = [];

            foreach ($this->children($groupEl, 'dive') as $diveEl) {
                $dives[] = $this->parseDive($diveEl, $knownDiveSiteIds, $knownTankIds, $knownMixIds);
            }

            if ($dives !== []) {
                $groups[] = new RepetitionGroup(id: $groupEl->getAttribute('id'), dives: $dives);
            }
        }

        if ($groups === []) {
            return null;
        }

        return new ProfileData(repetitionGroups: $groups);
    }

    /**
     * @param string[] $knownDiveSiteIds
     * @param string[] $knownTankIds
     * @param string[] $knownMixIds
     */
    private function parseDive(
        \DOMElement $diveEl,
        array $knownDiveSiteIds,
        array $knownTankIds,
        array $knownMixIds,
    ): Dive {
        $id = $diveEl->getAttribute('id');
        $beforeEl = $this->child($diveEl, 'informationbeforedive');

        if ($beforeEl === null) {
            throw new ParseException("Missing <informationbeforedive> in <dive id=\"{$id}\">.");
        }

        $samplesEl = $this->child($diveEl, 'samples');
        $waypoints = $samplesEl !== null
            ? array_map(fn (\DOMElement $wp): Waypoint => $this->parseWaypoint($wp), $this->children($samplesEl, 'waypoint'))
            : [];

        $afterEl = $this->child($diveEl, 'informationafterdive');

        return new Dive(
            id: $id,
            informationBeforeDive: $this->parseInformationBeforeDive($beforeEl, $knownDiveSiteIds),
            samples: $waypoints,
            informationAfterDive: $afterEl !== null ? $this->parseInformationAfterDive($afterEl) : null,
            tankData: $this->parseTankData($diveEl, $knownTankIds, $knownMixIds),
            applicationData: $this->parseApplicationData($diveEl),
        );
    }

    /** @param string[] $knownDiveSiteIds */
    private function parseInformationBeforeDive(\DOMElement $beforeEl, array $knownDiveSiteIds): InformationBeforeDive
    {
        $equipmentUsedEl = $this->child($beforeEl, 'equipmentused');
        $tripMembershipEl = $this->child($beforeEl, 'tripmembership');
        $alcoholEl = $this->child($beforeEl, 'alcoholbeforedive');
        $medicalEl = $this->child($beforeEl, 'medicalbeforedive');
        $priceEl = $this->child($beforeEl, 'price');
        $inputProfileEl = $this->child($beforeEl, 'inputprofile');
        $plannedProfileEl = $this->child($beforeEl, 'plannedprofile');
        $surfaceIntervalEl = $this->child($beforeEl, 'surfaceintervalbeforedive');

        return new InformationBeforeDive(
            datetime: new \DateTimeImmutable($this->require($beforeEl, 'datetime')),
            diveNumber: $this->int($beforeEl, 'divenumber'),
            diveSiteRef: $this->findDiveSiteLinkRef($beforeEl, $knownDiveSiteIds),
            diveNumberOfDay: $this->int($beforeEl, 'divenumberofday'),
            internalDiveNumber: $this->int($beforeEl, 'internaldivenumber'),
            airTemperature: $this->float($beforeEl, 'airtemperature'),
            surfaceIntervalBeforeDive: $surfaceIntervalEl !== null ? $this->parseSurfaceInterval($surfaceIntervalEl) : null,
            altitude: $this->float($beforeEl, 'altitude'),
            equipmentUsed: $equipmentUsedEl !== null ? new EquipmentUsed(
                leadQuantity: $this->float($equipmentUsedEl, 'leadquantity'),
                linkRefs: array_map(
                    static fn (\DOMElement $link): string => $link->getAttribute('ref'),
                    $this->children($equipmentUsedEl, 'link'),
                ),
            ) : null,
            apparatus: $this->parseEnum(Apparatus::class, $beforeEl, 'apparatus'),
            platform: $this->parseEnum(Platform::class, $beforeEl, 'platform'),
            purpose: $this->parseEnum(Purpose::class, $beforeEl, 'purpose'),
            stateOfRestBeforeDive: $this->parseEnum(StateOfRestBeforeDive::class, $beforeEl, 'stateofrestbeforedive'),
            tripMembershipRef: $tripMembershipEl?->getAttribute('ref'),
            alcoholBeforeDive: $alcoholEl !== null ? $this->parseDrugs($alcoholEl, 'drink') : [],
            medicalBeforeDive: $medicalEl !== null ? $this->parseDrugs($medicalEl, 'medicine') : [],
            noSuit: $this->marker($beforeEl, 'nosuit'),
            price: $priceEl !== null ? new Price(amount: (float) trim($priceEl->textContent), currency: $this->attr($priceEl, 'currency')) : null,
            inputProfile: $inputProfileEl !== null ? new InputProfile(
                linkRefs: array_map(
                    static fn (\DOMElement $link): string => $link->getAttribute('ref'),
                    $this->children($inputProfileEl, 'link'),
                ),
                waypoints: array_map(
                    fn (\DOMElement $wp): Waypoint => $this->parseWaypoint($wp),
                    $this->children($inputProfileEl, 'waypoint'),
                ),
            ) : null,
            plannedProfile: $plannedProfileEl !== null
                ? array_map(fn (\DOMElement $wp): Waypoint => $this->parseWaypoint($wp), $this->children($plannedProfileEl, 'waypoint'))
                : [],
            surfacePressure: $this->float($beforeEl, 'surfacepressure'),
        );
    }

    /** @return Drug[] */
    private function parseDrugs(\DOMElement $parent, string $elementName): array
    {
        return array_map(
            function (\DOMElement $el): Drug {
                $periodicallyTakenStr = $this->text($el, 'periodicallytaken');

                return new Drug(
                    name: $this->require($el, 'name'),
                    aliasNames: $this->texts($el, 'aliasname'),
                    periodicallyTaken: $periodicallyTakenStr !== null ? $periodicallyTakenStr === 'yes' : null,
                    timeSpanBeforeDive: $this->float($el, 'timespanbeforedive'),
                    notes: $this->parseNotes($el),
                );
            },
            $this->children($parent, $elementName),
        );
    }

    private function parseInformationAfterDive(\DOMElement $afterEl): InformationAfterDive
    {
        $surfaceIntervalEl = $this->child($afterEl, 'surfaceintervalafterdive');
        $ratings = $this->parseRatings($afterEl);
        $anySymptomsEl = $this->child($afterEl, 'anysymptoms');
        $globalAlarmsEl = $this->child($afterEl, 'globalalarmsgiven');
        $observationsEl = $this->child($afterEl, 'observations');

        return new InformationAfterDive(
            greatestDepth: $this->float($afterEl, 'greatestdepth') ?? 0.0,
            diveDuration: $this->float($afterEl, 'diveduration') ?? 0.0,
            averageDepth: $this->float($afterEl, 'averagedepth'),
            notes: $this->parseNotes($afterEl),
            surfaceIntervalAfterDive: $surfaceIntervalEl !== null ? $this->parseSurfaceInterval($surfaceIntervalEl) : null,
            lowestTemperature: $this->float($afterEl, 'lowesttemperature'),
            visibility: $this->float($afterEl, 'visibility'),
            current: $this->parseEnum(Current::class, $afterEl, 'current'),
            divePlan: $this->parseEnum(DivePlan::class, $afterEl, 'diveplan'),
            equipmentMalfunction: $this->parseEnum(EquipmentMalfunction::class, $afterEl, 'equipmentmalfunction'),
            pressureDrop: $this->float($afterEl, 'pressuredrop'),
            problems: $this->parseEnum(Problems::class, $afterEl, 'problems'),
            program: $this->parseEnum(Program::class, $afterEl, 'program'),
            thermalComfort: $this->parseEnum(ThermalComfort::class, $afterEl, 'thermalcomfort'),
            workload: $this->parseEnum(Workload::class, $afterEl, 'workload'),
            desaturationTime: $this->float($afterEl, 'desaturationtime'),
            noFlightTime: $this->float($afterEl, 'noflighttime'),
            rating: $ratings[0] ?? null,
            anySymptoms: $anySymptomsEl !== null
                ? array_filter(array_map(fn (\DOMElement $n): ?Notes => $this->parseNotesElement($n), $this->children($anySymptomsEl, 'notes')))
                : [],
            diveTable: $this->parseEnum(DiveTable::class, $afterEl, 'divetable'),
            globalAlarmsGiven: $globalAlarmsEl !== null
                ? array_map(fn (\DOMElement $ga): GlobalAlarm => $this->enumFrom(GlobalAlarm::class, trim($ga->textContent), '<globalalarm>'), $this->children($globalAlarmsEl, 'globalalarm'))
                : [],
            highestPo2: $this->float($afterEl, 'highestpo2'),
            observations: $observationsEl !== null ? new Observations(
                fauna: $this->parseFauna($observationsEl),
                flora: $this->parseFlora($observationsEl),
                notes: $this->parseNotes($observationsEl),
            ) : null,
        );
    }

    /**
     * @param string[] $knownTankIds
     * @param string[] $knownMixIds
     * @return TankData[]
     */
    private function parseTankData(\DOMElement $diveEl, array $knownTankIds, array $knownMixIds): array
    {
        $result = [];

        foreach ($this->children($diveEl, 'tankdata') as $tankDataEl) {
            $links = $this->children($tankDataEl, 'link');

            $result[] = new TankData(
                tankRef: $this->resolveLinkRef($links, $knownTankIds),
                mixRef: $this->resolveLinkRef($links, $knownMixIds),
                tankVolume: $this->float($tankDataEl, 'tankvolume'),
                tankPressureBegin: $this->float($tankDataEl, 'tankpressurebegin'),
                tankPressureEnd: $this->float($tankDataEl, 'tankpressureend'),
                breathingConsumptionVolume: $this->float($tankDataEl, 'breathingconsumptionvolume'),
            );
        }

        return $result;
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
}
