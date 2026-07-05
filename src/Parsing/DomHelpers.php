<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Parsing;

use Kreitje\UddfGenerator\Common\Address;
use Kreitje\UddfGenerator\Common\Contact;
use Kreitje\UddfGenerator\Common\Dimension;
use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\Common\Price;
use Kreitje\UddfGenerator\Common\Rating;
use Kreitje\UddfGenerator\DiveSite\Fauna;
use Kreitje\UddfGenerator\DiveSite\Flora;
use Kreitje\UddfGenerator\DiveSite\Invertebrata;
use Kreitje\UddfGenerator\DiveSite\SingleLifeForm;
use Kreitje\UddfGenerator\DiveSite\Species;
use Kreitje\UddfGenerator\DiveSite\Vertebrata;
use Kreitje\UddfGenerator\Enum\AbundanceOccurence;
use Kreitje\UddfGenerator\Enum\AbundanceQuality;
use Kreitje\UddfGenerator\Enum\AlarmType;
use Kreitje\UddfGenerator\Enum\DecoStopKind;
use Kreitje\UddfGenerator\Enum\DiveMode;
use Kreitje\UddfGenerator\Enum\SetPo2SetBy;
use Kreitje\UddfGenerator\Enum\Transportation;
use Kreitje\UddfGenerator\ProfileData\Alarm;
use Kreitje\UddfGenerator\ProfileData\ApplicationData;
use Kreitje\UddfGenerator\ProfileData\BatteryChargeCondition;
use Kreitje\UddfGenerator\ProfileData\DecoStop;
use Kreitje\UddfGenerator\ProfileData\ExposureToAltitude;
use Kreitje\UddfGenerator\ProfileData\GradientFactor;
use Kreitje\UddfGenerator\ProfileData\MeasuredPo2Reading;
use Kreitje\UddfGenerator\ProfileData\SetPo2;
use Kreitje\UddfGenerator\ProfileData\SurfaceInterval;
use Kreitje\UddfGenerator\ProfileData\TankPressureReading;
use Kreitje\UddfGenerator\ProfileData\WayAltitude;
use Kreitje\UddfGenerator\ProfileData\Waypoint;
use Kreitje\UddfGenerator\Enum\Dominance;
use Kreitje\UddfGenerator\Enum\GlobalLightIntensity;
use Kreitje\UddfGenerator\Enum\LifeStage;
use Kreitje\UddfGenerator\Enum\Sex;
use Kreitje\UddfGenerator\Generator\Manufacturer;
use Kreitje\UddfGenerator\ParseException;

trait DomHelpers
{
    protected function parseSurfaceInterval(\DOMElement $el): SurfaceInterval
    {
        $infinityPresent = $this->marker($el, 'infinity');
        $passedTime = $this->int($el, 'passedtime');
        $exposureEl = $this->child($el, 'exposuretoaltitude');

        return new SurfaceInterval(
            infinity: $infinityPresent,
            passedTime: $infinityPresent ? null : $passedTime,
            wayAltitudes: array_map(
                fn (\DOMElement $wa): WayAltitude => new WayAltitude(
                    value: (float) trim($wa->textContent),
                    wayTime: $this->attrFloat($wa, 'waytime') ?? 0.0,
                ),
                $this->children($el, 'wayaltitude'),
            ),
            exposureToAltitude: $exposureEl !== null ? new ExposureToAltitude(
                transportation: $this->parseEnum(Transportation::class, $exposureEl, 'transportation')
                    ?? Transportation::GroundTransportation,
                surfaceIntervalBeforeAltitudeExposure: $this->int($exposureEl, 'surfaceintervalbeforealtitudeexposure'),
                dateOfFlight: $this->parseEncapsulatedDateTime($exposureEl, 'dateofflight'),
                altitudeOfExposure: $this->int($exposureEl, 'altitudeofexposure'),
                totalLengthOfExposure: $this->int($exposureEl, 'totallengthofexposure'),
            ) : null,
        );
    }

    protected function parseWaypoint(\DOMElement $wpEl): Waypoint
    {
        $switchMixEl = $this->child($wpEl, 'switchmix');
        $setPo2El = $this->child($wpEl, 'setpo2');
        $diveModeEl = $this->child($wpEl, 'divemode');
        $gradientFactorEl = $this->child($wpEl, 'gradientfactor');

        return new Waypoint(
            depth: $this->float($wpEl, 'depth') ?? 0.0,
            diveTime: $this->float($wpEl, 'divetime') ?? 0.0,
            temperature: $this->float($wpEl, 'temperature'),
            tankPressures: array_map(
                fn (\DOMElement $tp): TankPressureReading => new TankPressureReading(
                    value: (float) trim($tp->textContent),
                    ref: $this->attr($tp, 'ref'),
                ),
                $this->children($wpEl, 'tankpressure'),
            ),
            switchMixRef: ($switchMixEl?->getAttribute('ref') ?: null),
            alarms: array_map(
                fn (\DOMElement $a): Alarm => new Alarm(
                    type: $this->enumFrom(AlarmType::class, trim($a->textContent), '<alarm>'),
                    level: $this->attrFloat($a, 'level'),
                ),
                $this->children($wpEl, 'alarm'),
            ),
            batteryChargeConditions: array_map(
                fn (\DOMElement $b): BatteryChargeCondition => new BatteryChargeCondition(
                    value: (float) trim($b->textContent),
                    deviceRef: $b->getAttribute('deviceref'),
                    tankRef: $this->attr($b, 'tankref'),
                ),
                $this->children($wpEl, 'batterychargecondition'),
            ),
            cns: $this->float($wpEl, 'cns'),
            decoStops: array_map(
                fn (\DOMElement $d): DecoStop => new DecoStop(
                    kind: $this->parseEnumAttr(DecoStopKind::class, $d, 'kind') ?? DecoStopKind::Safety,
                    decoDepth: $this->attrFloat($d, 'decodepth') ?? 0.0,
                    duration: $this->attrFloat($d, 'duration') ?? 0.0,
                ),
                $this->children($wpEl, 'decostop'),
            ),
            bodyTemperature: $this->float($wpEl, 'bodytemperature'),
            calculatedPo2: $this->float($wpEl, 'calculatedpo2'),
            heading: $this->float($wpEl, 'heading'),
            heartRate: $this->float($wpEl, 'heartrate'),
            otu: $this->float($wpEl, 'otu'),
            pulseRate: $this->float($wpEl, 'pulserate'),
            remainingBottomTime: $this->float($wpEl, 'remainingbottomtime'),
            remainingO2Time: $this->float($wpEl, 'remainingo2time'),
            setMarker: $this->text($wpEl, 'setmarker'),
            setPo2: $setPo2El !== null ? new SetPo2(
                value: (float) trim($setPo2El->textContent),
                setBy: $this->parseEnumAttr(SetPo2SetBy::class, $setPo2El, 'setby') ?? SetPo2SetBy::Computer,
            ) : null,
            diveMode: $diveModeEl !== null ? $this->parseEnumAttr(DiveMode::class, $diveModeEl, 'type') : null,
            gradientFactor: $gradientFactorEl !== null ? new GradientFactor(
                value: (float) trim($gradientFactorEl->textContent),
                tissue: $this->attrInt($gradientFactorEl, 'tissue'),
            ) : null,
            measuredPo2Readings: array_map(
                fn (\DOMElement $mp): MeasuredPo2Reading => new MeasuredPo2Reading(
                    value: (float) trim($mp->textContent),
                    ref: $this->attr($mp, 'ref'),
                ),
                $this->children($wpEl, 'measuredpo2'),
            ),
            noDecoTime: $this->float($wpEl, 'nodecotime'),
        );
    }

    protected function parseApplicationData(\DOMElement $parent): ?ApplicationData
    {
        $el = $this->child($parent, 'applicationdata');

        if ($el === null) {
            return null;
        }

        $vendors = ['decotrainer', 'hargikas', 'heinrichsweikamp', 'tausim', 'tautabu'];
        $values = [];

        foreach ($vendors as $vendor) {
            $vendorEl = $this->child($el, $vendor);

            if ($vendorEl === null) {
                $values[$vendor] = null;
                continue;
            }

            $fragments = [];
            for ($node = $vendorEl->firstChild; $node !== null; $node = $node->nextSibling) {
                if ($node instanceof \DOMElement) {
                    $fragments[] = $vendorEl->ownerDocument->saveXML($node);
                }
            }

            $values[$vendor] = $fragments;
        }

        return new ApplicationData(...$values);
    }

    protected function parseFauna(\DOMElement $parent): ?Fauna
    {
        $el = $this->child($parent, 'fauna');

        if ($el === null) {
            return null;
        }

        $invertebrataEl = $this->child($el, 'invertebrata');
        $vertebrataEl = $this->child($el, 'vertebrata');

        return new Fauna(
            invertebrata: $invertebrataEl !== null ? new Invertebrata(
                porifera: $this->parseSingleLifeForm($invertebrataEl, 'porifera'),
                coelenterata: $this->parseSingleLifeForm($invertebrataEl, 'coelenterata'),
                cnidaria: $this->parseSingleLifeForm($invertebrataEl, 'cnidaria'),
                ctenophora: $this->parseSingleLifeForm($invertebrataEl, 'ctenophora'),
                plathelminthes: $this->parseSingleLifeForm($invertebrataEl, 'plathelminthes'),
                bryozoa: $this->parseSingleLifeForm($invertebrataEl, 'bryozoa'),
                phoronidea: $this->parseSingleLifeForm($invertebrataEl, 'phoronidea'),
                ascidiacea: $this->parseSingleLifeForm($invertebrataEl, 'ascidiacea'),
                echinodermata: $this->parseSingleLifeForm($invertebrataEl, 'echinodermata'),
                mollusca: $this->parseSingleLifeForm($invertebrataEl, 'mollusca'),
                crustacea: $this->parseSingleLifeForm($invertebrataEl, 'crustacea'),
                invertebrataVarious: $this->parseSingleLifeForm($invertebrataEl, 'invertebratavarious'),
            ) : null,
            vertebrata: $vertebrataEl !== null ? new Vertebrata(
                chondrichthyes: $this->parseSingleLifeForm($vertebrataEl, 'chondrichthyes'),
                osteichthyes: $this->parseSingleLifeForm($vertebrataEl, 'osteichthyes'),
                mammalia: $this->parseSingleLifeForm($vertebrataEl, 'mammalia'),
                amphibia: $this->parseSingleLifeForm($vertebrataEl, 'amphibia'),
                reptilia: $this->parseSingleLifeForm($vertebrataEl, 'reptilia'),
                vertebrataVarious: $this->parseSingleLifeForm($vertebrataEl, 'vertebratavarious'),
            ) : null,
            notes: $this->parseNotes($el),
        );
    }

    protected function parseFlora(\DOMElement $parent): ?Flora
    {
        $el = $this->child($parent, 'flora');

        if ($el === null) {
            return null;
        }

        return new Flora(
            rhodophyceae: $this->parseSingleLifeForm($el, 'rhodophyceae'),
            phaeophyceae: $this->parseSingleLifeForm($el, 'phaeophyceae'),
            chlorophyceae: $this->parseSingleLifeForm($el, 'chlorophyceae'),
            spermatophyta: $this->parseSingleLifeForm($el, 'spermatophyta'),
            floraVarious: $this->parseSingleLifeForm($el, 'floravarious'),
            notes: $this->parseNotes($el),
        );
    }

    protected function parseSingleLifeForm(\DOMElement $parent, string $elementName): ?SingleLifeForm
    {
        $el = $this->child($parent, $elementName);

        if ($el === null) {
            return null;
        }

        return new SingleLifeForm(
            species: array_map(
                fn (\DOMElement $speciesEl): Species => $this->parseSpecies($speciesEl),
                $this->children($el, 'species'),
            ),
        );
    }

    protected function parseSpecies(\DOMElement $el): Species
    {
        $abundanceEl = $this->child($el, 'abundance');
        $lightIntensityEl = $this->child($el, 'lightintensity');

        return new Species(
            id: $el->getAttribute('id'),
            trivialName: $this->text($el, 'trivialname'),
            scientificName: $this->text($el, 'scientificname'),
            abundanceValue: $abundanceEl !== null ? (int) trim($abundanceEl->textContent) : null,
            abundanceQuality: $abundanceEl !== null ? $this->parseEnumAttr(AbundanceQuality::class, $abundanceEl, 'quality') : null,
            abundanceOccurence: $abundanceEl !== null ? $this->parseEnumAttr(AbundanceOccurence::class, $abundanceEl, 'occurence') : null,
            sex: $this->parseEnum(Sex::class, $el, 'sex'),
            lifeStage: $this->parseEnum(LifeStage::class, $el, 'lifestage'),
            lightIntensity: $lightIntensityEl !== null
                ? $this->enumFrom(GlobalLightIntensity::class, trim($lightIntensityEl->textContent), '<lightintensity>')
                : null,
            lightIntensityLux: $lightIntensityEl !== null ? $this->attrFloat($lightIntensityEl, 'lux') : null,
            age: $this->int($el, 'age'),
            dominance: $this->parseEnum(Dominance::class, $el, 'dominance'),
            size: $this->float($el, 'size'),
            notes: $this->parseNotes($el),
        );
    }

    protected function parseManufacturer(\DOMElement $parent): ?Manufacturer
    {
        $el = $this->child($parent, 'manufacturer');

        if ($el === null) {
            return null;
        }

        return $this->parseManufacturerElement($el);
    }

    protected function parseManufacturerElement(\DOMElement $el): Manufacturer
    {
        return new Manufacturer(
            id: $el->getAttribute('id'),
            name: $this->require($el, 'name'),
            aliasNames: $this->texts($el, 'aliasname'),
            address: $this->parseAddress($el),
            contact: $this->parseContact($el),
        );
    }
    protected function parseAddress(\DOMElement $parent): ?Address
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

    protected function parseContact(\DOMElement $parent): ?Contact
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

    protected function parseNotes(\DOMElement $parent): ?Notes
    {
        $el = $this->child($parent, 'notes');

        if ($el === null) {
            return null;
        }

        return $this->parseNotesElement($el);
    }

    protected function parseNotesElement(\DOMElement $el): ?Notes
    {
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

    protected function parseEncapsulatedDateTime(\DOMElement $parent, string $localName): ?\DateTimeImmutable
    {
        $wrapper = $this->child($parent, $localName);

        if ($wrapper === null) {
            return null;
        }

        $value = $this->text($wrapper, 'datetime');

        return $value !== null ? new \DateTimeImmutable($value) : null;
    }

    protected function parsePrice(\DOMElement $parent, string $localName): ?Price
    {
        $el = $this->child($parent, $localName);

        if ($el === null) {
            return null;
        }

        return new Price(
            amount: (float) trim($el->textContent),
            currency: $this->attr($el, 'currency'),
        );
    }

    protected function parseDimension(\DOMElement $parent): ?Dimension
    {
        $el = $this->child($parent, 'shipdimension');

        if ($el === null) {
            return null;
        }

        return new Dimension(
            length: $this->float($el, 'length'),
            beam: $this->float($el, 'beam'),
            draught: $this->float($el, 'draught'),
            displacement: $this->float($el, 'displacement'),
            tonnage: $this->float($el, 'tonnage'),
        );
    }

    /** @return Rating[] */
    protected function parseRatings(\DOMElement $parent): array
    {
        $ratings = [];

        foreach ($this->children($parent, 'rating') as $el) {
            $value = $this->int($el, 'ratingvalue');

            if ($value === null) {
                continue;
            }

            $datetimeStr = $this->text($el, 'datetime');

            $ratings[] = new Rating(
                value: $value,
                datetime: $datetimeStr !== null ? new \DateTimeImmutable($datetimeStr) : null,
            );
        }

        return $ratings;
    }
    protected function child(\DOMElement $parent, string $localName): ?\DOMElement
    {
        for ($node = $parent->firstChild; $node !== null; $node = $node->nextSibling) {
            if ($node instanceof \DOMElement && $node->localName === $localName) {
                return $node;
            }
        }

        return null;
    }

    /** @return \DOMElement[] */
    protected function children(\DOMElement $parent, string $localName): array
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
    protected function texts(\DOMElement $parent, string $localName): array
    {
        return array_map(
            static fn (\DOMElement $el): string => trim($el->textContent),
            $this->children($parent, $localName),
        );
    }

    protected function text(\DOMElement $parent, string $localName): ?string
    {
        $el = $this->child($parent, $localName);

        if ($el === null) {
            return null;
        }

        $value = trim($el->textContent);

        return $value !== '' ? $value : null;
    }

    protected function require(\DOMElement $parent, string $localName): string
    {
        $el = $this->child($parent, $localName);

        if ($el === null) {
            throw new ParseException("Missing required element <{$localName}> inside <{$parent->localName}>.");
        }

        return trim($el->textContent);
    }

    protected function float(\DOMElement $parent, string $localName): ?float
    {
        $value = $this->text($parent, $localName);

        return $value !== null ? (float) $value : null;
    }

    protected function int(\DOMElement $parent, string $localName): ?int
    {
        $value = $this->text($parent, $localName);

        return $value !== null ? (int) $value : null;
    }

    /** Presence of a marker/empty element (e.g. <infinity/>, <nosuit/>) as a boolean flag. */
    protected function marker(\DOMElement $parent, string $localName): bool
    {
        return $this->child($parent, $localName) !== null;
    }

    protected function attr(\DOMElement $el, string $name): ?string
    {
        return $el->hasAttribute($name) ? $el->getAttribute($name) : null;
    }

    protected function attrFloat(\DOMElement $el, string $name): ?float
    {
        $value = $this->attr($el, $name);

        return $value !== null && $value !== '' ? (float) $value : null;
    }

    protected function attrInt(\DOMElement $el, string $name): ?int
    {
        $value = $this->attr($el, $name);

        return $value !== null && $value !== '' ? (int) $value : null;
    }

    /**
     * @param \DOMElement[] $links
     * @param string[] $knownIds
     */
    protected function resolveLinkRef(array $links, array $knownIds): ?string
    {
        foreach ($links as $link) {
            $ref = $link->getAttribute('ref');
            if (in_array($ref, $knownIds, true)) {
                return $ref;
            }
        }

        return null;
    }

    /**
     * @template T of \BackedEnum
     * @param class-string<T> $enumClass
     * @return T|null
     */
    protected function parseEnum(string $enumClass, \DOMElement $parent, string $localName): ?\BackedEnum
    {
        $value = $this->text($parent, $localName);

        if ($value === null) {
            return null;
        }

        return $this->enumFrom($enumClass, $value, "<{$localName}>");
    }

    /**
     * @template T of \BackedEnum
     * @param class-string<T> $enumClass
     * @return T|null
     */
    protected function parseEnumAttr(string $enumClass, \DOMElement $el, string $attrName): ?\BackedEnum
    {
        $value = $this->attr($el, $attrName);

        if ($value === null || $value === '') {
            return null;
        }

        return $this->enumFrom($enumClass, $value, "attribute \"{$attrName}\"");
    }

    /**
     * @template T of \BackedEnum
     * @param class-string<T> $enumClass
     * @return T
     */
    private function enumFrom(string $enumClass, string $value, string $context): \BackedEnum
    {
        $case = $enumClass::tryFrom($value);

        if ($case === null) {
            $valid = implode(', ', array_map(static fn (\BackedEnum $c): string => (string) $c->value, $enumClass::cases()));
            throw new ParseException("Invalid value \"{$value}\" for {$context}; expected one of: {$valid}.");
        }

        return $case;
    }
}
