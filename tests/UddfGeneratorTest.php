<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Tests;

use Kreitje\UddfGenerator\Common\Address;
use Kreitje\UddfGenerator\Common\Contact;
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
use Kreitje\UddfGenerator\ProfileData\TankPressureReading;
use Kreitje\UddfGenerator\ProfileData\Waypoint;
use Kreitje\UddfGenerator\UddfGenerator;
use PHPUnit\Framework\TestCase;

final class UddfGeneratorTest extends TestCase
{
    private function buildFullUddf(): UddfGenerator
    {
        return new UddfGenerator(
            generator: new Generator(
                name: 'Test App',
                version: '1.0.0',
                datetime: new \DateTimeImmutable('2024-01-15T10:00:00'),
                manufacturer: new Manufacturer(
                    id: 'test_manufacturer',
                    name: 'Test Corp',
                    contact: new Contact(emails: ['test@example.com']),
                ),
            ),
            diver: new Diver(
                owner: new Owner(
                    id: 'owner',
                    personalData: new PersonalData(firstName: 'John', lastName: 'Doe'),
                    equipment: new Equipment(tanks: [
                        new Tank(id: 'tank_1', name: '12L Alu', volume: 12.0),
                    ]),
                ),
            ),
            diveSites: [
                new DiveSite(
                    id: 'site_001',
                    name: 'Great Barrier Reef',
                    geography: new Geography(
                        location: 'Queensland, Australia',
                        address: new Address(country: 'AU'),
                        latitude: -18.2871,
                        longitude: 147.6992,
                    ),
                ),
            ],
            gasDefinitions: new GasDefinitions([
                Mix::air(),
                Mix::nitrox('ean32', 0.32),
            ]),
            profileData: new ProfileData([
                new RepetitionGroup(
                    id: 'group_001',
                    dives: [
                        new Dive(
                            id: 'dive_001',
                            informationBeforeDive: new InformationBeforeDive(
                                datetime: new \DateTimeImmutable('2024-01-15T10:30:00'),
                                diveNumber: 42,
                                diveSiteRef: 'site_001',
                            ),
                            samples: [
                                new Waypoint(depth: 0.0, diveTime: 0, switchMixRef: 'air'),
                                new Waypoint(depth: 10.0, diveTime: 120, temperature: 298.15, tankPressures: [new TankPressureReading(value: 180.0)]),
                                new Waypoint(depth: 18.0, diveTime: 300, temperature: 296.15, tankPressures: [new TankPressureReading(value: 160.0)]),
                                new Waypoint(depth: 18.0, diveTime: 1500, temperature: 296.15, tankPressures: [new TankPressureReading(value: 80.0)]),
                                new Waypoint(depth: 5.0, diveTime: 1800, temperature: 297.15, tankPressures: [new TankPressureReading(value: 60.0)]),
                                new Waypoint(depth: 0.0, diveTime: 2100, tankPressures: [new TankPressureReading(value: 50.0)]),
                            ],
                        ),
                    ],
                ),
            ]),
        );
    }

    public function testGenerateReturnsValidXmlString(): void
    {
        $uddf = $this->buildFullUddf();
        $xml = $uddf->generate();

        $this->assertIsString($xml);
        $this->assertStringStartsWith('<?xml', $xml);
    }

    public function testRootElementHasCorrectVersion(): void
    {
        $xml = $this->buildFullUddf()->generate();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $root = $doc->documentElement;
        $this->assertSame('uddf', $root->tagName);
        $this->assertSame('3.2.3', $root->getAttribute('version'));
    }

    public function testGeneratorElementIsPresent(): void
    {
        $xml = $this->buildFullUddf()->generate();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $generators = $doc->getElementsByTagName('generator');
        $this->assertSame(1, $generators->length);

        $generator = $generators->item(0);
        $this->assertSame('Test App', $generator->getElementsByTagName('name')->item(0)->textContent);
        $this->assertSame('1.0.0', $generator->getElementsByTagName('version')->item(0)->textContent);
        $this->assertSame('2024-01-15T10:00:00', $generator->getElementsByTagName('datetime')->item(0)->textContent);
    }

    public function testDiverElementIsPresent(): void
    {
        $xml = $this->buildFullUddf()->generate();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $divers = $doc->getElementsByTagName('diver');
        $this->assertSame(1, $divers->length);

        $personal = $doc->getElementsByTagName('personal')->item(0);
        $this->assertSame('John', $personal->getElementsByTagName('firstname')->item(0)->textContent);
        $this->assertSame('Doe', $personal->getElementsByTagName('lastname')->item(0)->textContent);
    }

    public function testDiveSiteElementIsPresent(): void
    {
        $xml = $this->buildFullUddf()->generate();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $sites = $doc->getElementsByTagName('site');
        $this->assertSame(1, $sites->length);

        $site = $sites->item(0);
        $this->assertSame('site_001', $site->getAttribute('id'));
        $this->assertSame('Great Barrier Reef', $site->getElementsByTagName('name')->item(0)->textContent);
    }

    public function testGasDefinitionsElementIsPresent(): void
    {
        $xml = $this->buildFullUddf()->generate();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $mixes = $doc->getElementsByTagName('mix');
        $this->assertSame(2, $mixes->length);

        $air = $mixes->item(0);
        $this->assertSame('air', $air->getAttribute('id'));
        $this->assertSame('Air', $air->getElementsByTagName('name')->item(0)->textContent);
        $this->assertSame('0.21', $air->getElementsByTagName('o2')->item(0)->textContent);
    }

    public function testProfileDataElementIsPresent(): void
    {
        $xml = $this->buildFullUddf()->generate();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $dives = $doc->getElementsByTagName('dive');
        $this->assertSame(1, $dives->length);

        $dive = $dives->item(0);
        $this->assertSame('dive_001', $dive->getAttribute('id'));
    }

    public function testWaypointsAreGeneratedCorrectly(): void
    {
        $xml = $this->buildFullUddf()->generate();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $waypoints = $doc->getElementsByTagName('waypoint');
        $this->assertSame(6, $waypoints->length);

        $first = $waypoints->item(0);
        $this->assertSame('0', $first->getElementsByTagName('depth')->item(0)->textContent);
        $this->assertSame('0', $first->getElementsByTagName('divetime')->item(0)->textContent);

        $switchMix = $first->getElementsByTagName('switchmix')->item(0);
        $this->assertNotNull($switchMix);
        $this->assertSame('air', $switchMix->getAttribute('ref'));
    }

    public function testInformationAfterDiveIsAutoComputed(): void
    {
        $xml = $this->buildFullUddf()->generate();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $afterDive = $doc->getElementsByTagName('informationafterdive')->item(0);
        $this->assertNotNull($afterDive);
        $this->assertSame('18', $afterDive->getElementsByTagName('greatestdepth')->item(0)->textContent);
        $this->assertSame('2100', $afterDive->getElementsByTagName('diveduration')->item(0)->textContent);
    }

    public function testMinimalUddfWithOnlyGenerator(): void
    {
        $uddf = new UddfGenerator(
            generator: new Generator(name: 'MinApp', version: '0.1'),
        );

        $xml = $uddf->generate();
        $this->assertIsString($xml);

        $doc = new \DOMDocument();
        $this->assertTrue($doc->loadXML($xml));
        $this->assertSame(0, $doc->getElementsByTagName('diver')->length);
        $this->assertSame(0, $doc->getElementsByTagName('gasdefinitions')->length);
    }

    public function testMixNamedConstructors(): void
    {
        $air = Mix::air();
        $this->assertSame('air', $air->id);
        $this->assertSame(0.21, $air->o2);
        $this->assertSame(0.79, $air->n2);

        $nitrox = Mix::nitrox('ean36', 0.36);
        $this->assertSame('EAN36', $nitrox->name);
        $this->assertEqualsWithDelta(0.64, $nitrox->n2, 0.001);

        $trimix = Mix::trimix('tx21/35', 0.21, 0.35);
        $this->assertSame('Tx21/35', $trimix->name);
        $this->assertEqualsWithDelta(0.44, $trimix->n2, 0.001);
    }

    public function testWaypointNegativeDepthThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Waypoint(depth: -1.0, diveTime: 0);
    }

    public function testDiveRequiresAtLeastTwoWaypoints(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Dive(
            id: 'bad_dive',
            informationBeforeDive: new InformationBeforeDive(
                datetime: new \DateTimeImmutable(),
            ),
            samples: [new Waypoint(depth: 0.0, diveTime: 0)],
        );
    }

    public function testInformationAfterDiveFromWaypoints(): void
    {
        $info = InformationAfterDive::fromWaypoints(
            new Waypoint(depth: 0.0, diveTime: 0),
            new Waypoint(depth: 20.0, diveTime: 600),
            new Waypoint(depth: 0.0, diveTime: 1200),
        );

        $this->assertSame(20.0, $info->greatestDepth);
        $this->assertSame(1200.0, $info->diveDuration);
    }
}
