<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Tests;

use Kreitje\UddfGenerator\Gas\Mix;
use Kreitje\UddfGenerator\Generator\Generator;
use Kreitje\UddfGenerator\Generator\Manufacturer;
use Kreitje\UddfGenerator\Gas\GasDefinitions;
use Kreitje\UddfGenerator\Diver\Diver;
use Kreitje\UddfGenerator\Diver\Equipment;
use Kreitje\UddfGenerator\Diver\Owner;
use Kreitje\UddfGenerator\Diver\PersonalData;
use Kreitje\UddfGenerator\Diver\Tank;
use Kreitje\UddfGenerator\DiveSite\DiveSite;
use Kreitje\UddfGenerator\DiveSite\Geography;
use Kreitje\UddfGenerator\ParseException;
use Kreitje\UddfGenerator\ProfileData\Dive;
use Kreitje\UddfGenerator\ProfileData\InformationBeforeDive;
use Kreitje\UddfGenerator\ProfileData\ProfileData;
use Kreitje\UddfGenerator\ProfileData\RepetitionGroup;
use Kreitje\UddfGenerator\ProfileData\Waypoint;
use Kreitje\UddfGenerator\UddfGenerator;
use Kreitje\UddfGenerator\UddfParser;
use PHPUnit\Framework\TestCase;

final class UddfParserTest extends TestCase
{
    private UddfParser $parser;

    protected function setUp(): void
    {
        $this->parser = new UddfParser();
    }

    private function buildFullUddf(): UddfGenerator
    {
        return new UddfGenerator(
            generator: new Generator(
                name: 'Test App',
                version: '2.0.0',
                datetime: new \DateTimeImmutable('2024-06-01T08:00:00'),
                manufacturer: new Manufacturer(
                    id: 'mfr_1',
                    name: 'Acme Diving',
                    address: '1 Reef St',
                    phone: '+61400000000',
                    email: 'dev@acme.test',
                ),
            ),
            diver: new Diver(
                owner: new Owner(
                    id: 'owner_1',
                    personalData: new PersonalData(
                        firstName: 'Jane',
                        lastName: 'Doe',
                        birthdate: new \DateTimeImmutable('1990-05-15'),
                        sex: 'female',
                    ),
                    equipment: new Equipment(tanks: [
                        new Tank(id: 'tank_a', name: 'Alu80', volume: 11.1, workpressure: 207.0),
                    ]),
                ),
            ),
            diveSites: [
                new DiveSite(
                    id: 'site_gbr',
                    name: 'Great Barrier Reef',
                    geography: new Geography(
                        location: 'Queensland',
                        latitude: -18.2871,
                        longitude: 147.6992,
                        country: 'AU',
                    ),
                    notes: 'UNESCO World Heritage Site',
                ),
            ],
            gasDefinitions: new GasDefinitions([
                Mix::air(),
                Mix::nitrox('ean32', 0.32),
                Mix::trimix('tx21/35', 0.21, 0.35),
            ]),
            profileData: new ProfileData([
                new RepetitionGroup(
                    id: 'rg_1',
                    dives: [
                        new Dive(
                            id: 'dive_99',
                            informationBeforeDive: new InformationBeforeDive(
                                datetime: new \DateTimeImmutable('2024-06-01T09:00:00'),
                                diveNumber: 99,
                                diveSiteRef: 'site_gbr',
                                notes: 'Saw a manta ray',
                            ),
                            samples: [
                                new Waypoint(depth: 0.0, diveTime: 0, mixChangeRef: 'air'),
                                new Waypoint(depth: 25.0, diveTime: 180, temperature: 300.15, tankPressure: 190.0),
                                new Waypoint(depth: 25.0, diveTime: 1500, temperature: 298.15, tankPressure: 70.0),
                                new Waypoint(depth: 0.0, diveTime: 1800, tankPressure: 50.0),
                            ],
                        ),
                    ],
                ),
            ]),
        );
    }

    public function testRoundtripPreservesGenerator(): void
    {
        $original = $this->buildFullUddf();
        $parsed = $this->parser->parse($original->generate());

        $this->assertSame('Test App', $parsed->generator->name);
        $this->assertSame('2.0.0', $parsed->generator->version);
        $this->assertSame('2024-06-01T08:00:00', $parsed->generator->datetime?->format('Y-m-d\TH:i:s'));
    }

    public function testRoundtripPreservesManufacturer(): void
    {
        $parsed = $this->parser->parse($this->buildFullUddf()->generate());

        $mfr = $parsed->generator->manufacturer;
        $this->assertNotNull($mfr);
        $this->assertSame('mfr_1', $mfr->id);
        $this->assertSame('Acme Diving', $mfr->name);
        $this->assertSame('dev@acme.test', $mfr->email);
    }

    public function testRoundtripPreservesDiver(): void
    {
        $parsed = $this->parser->parse($this->buildFullUddf()->generate());

        $owner = $parsed->diver?->owner;
        $this->assertNotNull($owner);
        $this->assertSame('owner_1', $owner->id);
        $this->assertSame('Jane', $owner->personalData->firstName);
        $this->assertSame('Doe', $owner->personalData->lastName);
        $this->assertSame('1990-05-15', $owner->personalData->birthdate?->format('Y-m-d'));
        $this->assertSame('female', $owner->personalData->sex);
    }

    public function testRoundtripPreservesEquipment(): void
    {
        $parsed = $this->parser->parse($this->buildFullUddf()->generate());

        $tanks = $parsed->diver?->owner->equipment?->tanks ?? [];
        $this->assertCount(1, $tanks);
        $this->assertSame('tank_a', $tanks[0]->id);
        $this->assertSame('Alu80', $tanks[0]->name);
        $this->assertSame(11.1, $tanks[0]->volume);
        $this->assertSame(207.0, $tanks[0]->workpressure);
    }

    public function testRoundtripPreservesDiveSite(): void
    {
        $parsed = $this->parser->parse($this->buildFullUddf()->generate());

        $this->assertCount(1, $parsed->diveSites);
        $site = $parsed->diveSites[0];
        $this->assertSame('site_gbr', $site->id);
        $this->assertSame('Great Barrier Reef', $site->name);
        $this->assertSame('UNESCO World Heritage Site', $site->notes);

        $geo = $site->geography;
        $this->assertNotNull($geo);
        $this->assertSame('Queensland', $geo->location);
        $this->assertSame(-18.2871, $geo->latitude);
        $this->assertSame(147.6992, $geo->longitude);
        $this->assertSame('AU', $geo->country);
    }

    public function testRoundtripPreservesGasDefinitions(): void
    {
        $parsed = $this->parser->parse($this->buildFullUddf()->generate());

        $mixes = $parsed->gasDefinitions?->mixes ?? [];
        $this->assertCount(3, $mixes);

        $this->assertSame('air', $mixes[0]->id);
        $this->assertSame('Air', $mixes[0]->name);
        $this->assertSame(0.21, $mixes[0]->o2);
        $this->assertSame(0.79, $mixes[0]->n2);
        $this->assertSame(0.0, $mixes[0]->he);

        $this->assertSame('ean32', $mixes[1]->id);
        $this->assertSame('EAN32', $mixes[1]->name);

        $this->assertSame('tx21/35', $mixes[2]->id);
        $this->assertSame('Tx21/35', $mixes[2]->name);
        $this->assertSame(0.21, $mixes[2]->o2);
        $this->assertSame(0.35, $mixes[2]->he);
    }

    public function testRoundtripPreservesProfileData(): void
    {
        $parsed = $this->parser->parse($this->buildFullUddf()->generate());

        $groups = $parsed->profileData?->repetitionGroups ?? [];
        $this->assertCount(1, $groups);
        $this->assertSame('rg_1', $groups[0]->id);

        $dives = $groups[0]->dives;
        $this->assertCount(1, $dives);
        $this->assertSame('dive_99', $dives[0]->id);
    }

    public function testRoundtripPreservesInformationBeforeDive(): void
    {
        $parsed = $this->parser->parse($this->buildFullUddf()->generate());

        $before = $parsed->profileData?->repetitionGroups[0]->dives[0]->informationBeforeDive;
        $this->assertNotNull($before);
        $this->assertSame('2024-06-01T09:00:00', $before->datetime->format('Y-m-d\TH:i:s'));
        $this->assertSame(99, $before->diveNumber);
        $this->assertSame('site_gbr', $before->diveSiteRef);
        $this->assertSame('Saw a manta ray', $before->notes);
    }

    public function testRoundtripPreservesWaypoints(): void
    {
        $parsed = $this->parser->parse($this->buildFullUddf()->generate());

        $waypoints = $parsed->profileData?->repetitionGroups[0]->dives[0]->samples ?? [];
        $this->assertCount(4, $waypoints);

        $this->assertSame(0.0, $waypoints[0]->depth);
        $this->assertSame(0, $waypoints[0]->diveTime);
        $this->assertSame('air', $waypoints[0]->mixChangeRef);
        $this->assertNull($waypoints[0]->temperature);

        $this->assertSame(25.0, $waypoints[1]->depth);
        $this->assertSame(180, $waypoints[1]->diveTime);
        $this->assertSame(300.15, $waypoints[1]->temperature);
        $this->assertSame(190.0, $waypoints[1]->tankPressure);

        $this->assertSame(0.0, $waypoints[3]->depth);
        $this->assertSame(1800, $waypoints[3]->diveTime);
        $this->assertNull($waypoints[3]->mixChangeRef);
    }

    public function testRoundtripPreservesInformationAfterDive(): void
    {
        $parsed = $this->parser->parse($this->buildFullUddf()->generate());

        $after = $parsed->profileData?->repetitionGroups[0]->dives[0]->informationAfterDive;
        $this->assertNotNull($after);
        $this->assertSame(25.0, $after->greatestDepth);
        $this->assertSame(1800, $after->diveDuration);
    }

    public function testParseMinimalUddf(): void
    {
        $xml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <uddf version="3.2.3">
          <generator>
            <name>MinApp</name>
            <version>1.0</version>
          </generator>
        </uddf>
        XML;

        $uddf = $this->parser->parse($xml);

        $this->assertSame('MinApp', $uddf->generator->name);
        $this->assertSame('1.0', $uddf->generator->version);
        $this->assertNull($uddf->diver);
        $this->assertSame([], $uddf->diveSites);
        $this->assertNull($uddf->gasDefinitions);
        $this->assertNull($uddf->profileData);
    }

    public function testParseUddfWithoutNamespace(): void
    {
        $xml = <<<XML
        <?xml version="1.0"?>
        <uddf version="3.2.3">
          <generator>
            <name>NoNs App</name>
            <version>0.5</version>
          </generator>
        </uddf>
        XML;

        $uddf = $this->parser->parse($xml);

        $this->assertSame('NoNs App', $uddf->generator->name);
    }

    public function testParseInvalidXmlThrows(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessageMatches('/Failed to parse XML/');

        $this->parser->parse('<not valid xml>>');
    }

    public function testParseWrongRootElementThrows(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessageMatches('/Root element must be <uddf>/');

        $this->parser->parse('<dive><something/></dive>');
    }

    public function testParseMissingGeneratorThrows(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessageMatches('/Missing required <generator>/');

        $this->parser->parse('<uddf version="3.2.3"></uddf>');
    }

    public function testParseMissingGeneratorNameThrows(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessageMatches('/<name>/');

        $this->parser->parse('<uddf version="3.2.3"><generator><version>1.0</version></generator></uddf>');
    }

    public function testParseFileNotFoundThrows(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessageMatches('/File not found/');

        $this->parser->parseFile('/nonexistent/path/file.uddf');
    }

    public function testParsedUddfCanBeRegenerated(): void
    {
        $original = $this->buildFullUddf()->generate();
        $parsed = $this->parser->parse($original);
        $regenerated = $parsed->generate();

        // Both should parse to valid XML with the same structure
        $doc1 = new \DOMDocument();
        $doc1->loadXML($original);

        $doc2 = new \DOMDocument();
        $doc2->loadXML($regenerated);

        $this->assertSame(
            $doc1->getElementsByTagName('waypoint')->length,
            $doc2->getElementsByTagName('waypoint')->length,
        );

        $this->assertSame(
            $doc1->getElementsByTagName('mix')->length,
            $doc2->getElementsByTagName('mix')->length,
        );
    }
}
