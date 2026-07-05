<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323;

use Kreitje\UddfGenerator\ParseException;
use Kreitje\UddfGenerator\Spec\V323\Business\Business;
use Kreitje\UddfGenerator\Spec\V323\Common\Price;
use Kreitje\UddfGenerator\Spec\V323\Common\Shop;
use Kreitje\UddfGenerator\Spec\V323\Diver\Tank;
use Kreitje\UddfGenerator\Spec\V323\Enum\GeneratorType;
use Kreitje\UddfGenerator\Spec\V323\Enum\MeteringMethod;
use Kreitje\UddfGenerator\Spec\V323\DiveSite\DiveSite;
use Kreitje\UddfGenerator\Spec\V323\Gas\GasDefinitions;
use Kreitje\UddfGenerator\Spec\V323\Gas\Mix;
use Kreitje\UddfGenerator\Spec\V323\Generator\Generator;
use Kreitje\UddfGenerator\Spec\V323\Generator\Manufacturer;
use Kreitje\UddfGenerator\Spec\V323\Maker\Maker;
use Kreitje\UddfGenerator\Spec\V323\Media\Image;
use Kreitje\UddfGenerator\Spec\V323\Media\ImageData;
use Kreitje\UddfGenerator\Spec\V323\Media\Media;
use Kreitje\UddfGenerator\Spec\V323\Media\MediaData;
use Kreitje\UddfGenerator\Spec\V323\Parsing\DomHelpers;
use Kreitje\UddfGenerator\Spec\V323\Parsing\DecoModelParser;
use Kreitje\UddfGenerator\Spec\V323\Parsing\DiveComputerControlParser;
use Kreitje\UddfGenerator\Spec\V323\Parsing\DiveSiteParser;
use Kreitje\UddfGenerator\Spec\V323\Parsing\DiveTripParser;
use Kreitje\UddfGenerator\Spec\V323\Parsing\DiverParser;
use Kreitje\UddfGenerator\Spec\V323\Parsing\ProfileDataParser;
use Kreitje\UddfGenerator\Spec\V323\Parsing\TableGenerationParser;

final class UddfParser
{
    use DomHelpers;

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

        $diveSiteParser = new DiveSiteParser();
        $diveSites = $diveSiteParser->parseSites($root);
        $diveBases = $diveSiteParser->parseDiveBases($root);
        $diver = (new DiverParser())->parse($root);
        $gasDefinitions = $this->parseGasDefinitions($root);

        return new UddfGenerator(
            generator: $this->parseGenerator($root),
            media: $this->parseMediaData($root),
            maker: $this->parseMaker($root),
            business: $this->parseBusiness($root),
            diver: $diver,
            diveSites: $diveSites,
            diveBases: $diveBases,
            diveTrip: (new DiveTripParser())->parse($root),
            gasDefinitions: $gasDefinitions,
            decoModel: (new DecoModelParser())->parse($root),
            profileData: (new ProfileDataParser())->parse(
                $root,
                array_map(static fn (DiveSite $site): string => $site->id, $diveSites),
                array_map(static fn (Tank $tank): string => $tank->id, $diver?->owner->equipment?->tanks ?? []),
                array_map(static fn (Mix $mix): string => $mix->id, $gasDefinitions?->mixes ?? []),
            ),
            tableGeneration: (new TableGenerationParser())->parse($root),
            diveComputerControl: (new DiveComputerControlParser())->parse($root),
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

        $manufacturer = $this->parseManufacturer($el);
        $datetimeStr = $this->text($el, 'datetime');
        $linkEl = $this->child($el, 'link');

        return new Generator(
            name: $this->require($el, 'name'),
            aliasNames: $this->texts($el, 'aliasname'),
            type: $this->parseEnum(GeneratorType::class, $el, 'type'),
            linkRef: $linkEl?->getAttribute('ref'),
            manufacturer: $manufacturer,
            version: $this->text($el, 'version'),
            datetime: $datetimeStr !== null ? new \DateTimeImmutable($datetimeStr) : null,
        );
    }

    private function parseGasDefinitions(\DOMElement $root): ?GasDefinitions
    {
        $el = $this->child($root, 'gasdefinitions');

        if ($el === null) {
            return null;
        }

        $mixes = [];

        foreach ($this->children($el, 'mix') as $mixEl) {
            $priceEl = $this->child($mixEl, 'priceperlitre');

            $mixes[] = new Mix(
                id: $mixEl->getAttribute('id'),
                name: $this->require($mixEl, 'name'),
                o2: $this->float($mixEl, 'o2') ?? 0.0,
                n2: $this->float($mixEl, 'n2') ?? 0.0,
                he: $this->float($mixEl, 'he') ?? 0.0,
                aliasNames: $this->texts($mixEl, 'aliasname'),
                ar: $this->float($mixEl, 'ar'),
                h2: $this->float($mixEl, 'h2'),
                pricePerLitre: $priceEl !== null ? new Price(
                    amount: (float) trim($priceEl->textContent),
                    currency: $this->attr($priceEl, 'currency'),
                ) : null,
                maximumPo2: $this->float($mixEl, 'maximumpo2'),
                maximumOperationDepth: $this->float($mixEl, 'maximumoperationdepth'),
                equivalentAirDepth: $this->float($mixEl, 'equivalentairdepth'),
            );
        }

        if ($mixes === []) {
            return null;
        }

        return new GasDefinitions(mixes: $mixes);
    }

    private function parseMediaData(\DOMElement $root): ?MediaData
    {
        $el = $this->child($root, 'mediadata');

        if ($el === null) {
            return null;
        }

        return new MediaData(
            audio: array_map(fn (\DOMElement $e): Media => $this->parseMedia($e), $this->children($el, 'audio')),
            images: array_map(fn (\DOMElement $e): Image => $this->parseImage($e), $this->children($el, 'image')),
            video: array_map(fn (\DOMElement $e): Media => $this->parseMedia($e), $this->children($el, 'video')),
        );
    }

    private function parseMedia(\DOMElement $el): Media
    {
        return new Media(
            id: $el->getAttribute('id'),
            objectName: $this->require($el, 'objectname'),
            title: $this->text($el, 'title'),
        );
    }

    private function parseImage(\DOMElement $el): Image
    {
        $imageDataEl = $this->child($el, 'imagedata');

        return new Image(
            id: $el->getAttribute('id'),
            objectName: $this->require($el, 'objectname'),
            title: $this->text($el, 'title'),
            imageData: $imageDataEl !== null ? new ImageData(
                aperture: $this->float($imageDataEl, 'aperture'),
                datetime: ($dt = $this->text($imageDataEl, 'datetime')) !== null ? new \DateTimeImmutable($dt) : null,
                exposureCompensation: $this->float($imageDataEl, 'exposurecompensation'),
                filmSpeed: $this->int($imageDataEl, 'filmspeed'),
                focalLength: $this->float($imageDataEl, 'focallength'),
                focusingDistance: $this->float($imageDataEl, 'focusingdistance'),
                meteringMethod: $this->parseEnum(MeteringMethod::class, $imageDataEl, 'meteringmethod'),
                shutterSpeed: $this->float($imageDataEl, 'shutterspeed'),
            ) : null,
            height: $this->attrInt($el, 'height'),
            width: $this->attrInt($el, 'width'),
            format: $this->attr($el, 'format'),
        );
    }

    private function parseMaker(\DOMElement $root): ?Maker
    {
        $el = $this->child($root, 'maker');

        if ($el === null) {
            return null;
        }

        return new Maker(manufacturers: array_map(
            fn (\DOMElement $e): Manufacturer => $this->parseManufacturerElement($e),
            $this->children($el, 'manufacturer'),
        ));
    }

    private function parseBusiness(\DOMElement $root): ?Business
    {
        $el = $this->child($root, 'business');

        if ($el === null) {
            return null;
        }

        return new Business(shops: array_map(
            fn (\DOMElement $e): Shop => new Shop(
                id: $e->getAttribute('id'),
                name: $this->require($e, 'name'),
                aliasNames: $this->texts($e, 'aliasname'),
                address: $this->parseAddress($e),
                contact: $this->parseContact($e),
                notes: $this->parseNotes($e),
            ),
            $this->children($el, 'shop'),
        ));
    }
}
