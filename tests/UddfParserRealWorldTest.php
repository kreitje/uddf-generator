<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Tests;

use Kreitje\UddfGenerator\UddfParser;
use PHPUnit\Framework\TestCase;

final class UddfParserRealWorldTest extends TestCase
{
    private UddfParser $parser;

    protected function setUp(): void
    {
        $this->parser = new UddfParser();
    }

    /**
     * Real-world export captured from a MauiBluetoothTest dive computer.
     */
    private function fixtureXml(): string
    {
        return file_get_contents(__DIR__ . '/fixtures/0.uddf.xml');
    }

    public function testParsesWithoutThrowing(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $this->assertInstanceOf(\Kreitje\UddfGenerator\UddfGenerator::class, $uddf);
    }

    public function testGeneratorProperties(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $this->assertSame('MauiBluetoothTest', $uddf->generator->name);
        $this->assertSame('1.0', $uddf->generator->version);
        $this->assertSame('2026-07-03T22:53:12+00:00', $uddf->generator->datetime?->format('c'));
    }

    public function testManufacturerProperties(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $manufacturer = $uddf->generator->manufacturer;
        $this->assertNotNull($manufacturer);
        $this->assertSame('MauiBluetoothTest', $manufacturer->name);
        $this->assertSame('', $manufacturer->id);
        $this->assertNull($manufacturer->address);
        $this->assertNull($manufacturer->contact);
    }

    public function testNoDiverEquipmentOrDiveSitesPresent(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $this->assertNull($uddf->diver);
        $this->assertSame([], $uddf->diveSites);
    }

    public function testGasDefinitionsProperties(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $mixes = $uddf->gasDefinitions?->mixes ?? [];
        $this->assertCount(3, $mixes);

        foreach (['mix0', 'mix1', 'mix2'] as $i => $expectedId) {
            $this->assertSame($expectedId, $mixes[$i]->id);
            $this->assertSame('Air', $mixes[$i]->name);
            $this->assertSame(0.21, $mixes[$i]->o2);
            $this->assertSame(0.79, $mixes[$i]->n2);
            $this->assertSame(0.0, $mixes[$i]->he);
        }
    }

    public function testProfileDataStructure(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $groups = $uddf->profileData?->repetitionGroups ?? [];
        $this->assertCount(1, $groups);
        $this->assertSame('rg1', $groups[0]->id);

        $dives = $groups[0]->dives;
        $this->assertCount(1, $dives);
        $this->assertSame('dive-00820227B494F11B', $dives[0]->id);
    }

    public function testInformationBeforeDive(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $before = $uddf->profileData?->repetitionGroups[0]->dives[0]->informationBeforeDive;

        $this->assertNotNull($before);
        $this->assertSame('2020-11-07T14:00:00', $before->datetime->format('Y-m-d\TH:i:s'));
        $this->assertNull($before->diveNumber);
        $this->assertNull($before->diveSiteRef);
    }

    public function testWaypointCountAndSamples(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $waypoints = $uddf->profileData?->repetitionGroups[0]->dives[0]->samples ?? [];

        $this->assertCount(82, $waypoints);

        $first = $waypoints[0];
        $this->assertSame(1.9622, $first->depth);
        $this->assertSame(30.0, $first->diveTime);
        $this->assertSame(296.4833, $first->temperature);
        $this->assertSame([], $first->tankPressures);
        $this->assertNull($first->switchMixRef);

        $last = $waypoints[count($waypoints) - 1];
        $this->assertSame(0.0, $last->depth);
        $this->assertSame(2460.0, $last->diveTime);
        $this->assertNull($last->temperature);
    }

    public function testInformationAfterDive(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $after = $uddf->profileData?->repetitionGroups[0]->dives[0]->informationAfterDive;

        $this->assertNotNull($after);
        $this->assertSame(2.0574, $after->greatestDepth);
        $this->assertSame(2460.0, $after->diveDuration);
        $this->assertNull($after->averageDepth);
        $this->assertNull($after->notes);
    }

    /**
     * A dive-level <notes> element (sibling of <informationafterdive>) is not
     * valid per the UDDF 3.2.3 schema — neither <dive> nor
     * <informationbeforedive> define a notes child, only <informationafterdive>
     * does. This fixture's "Dive mode: OpenCircuit" / "Raw byte count: 712"
     * <para> notes are therefore silently dropped rather than raising a
     * ParseException.
     */
    public function testDiveLevelNotesAreNotExposedAnywhere(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());
        $dive = $uddf->profileData?->repetitionGroups[0]->dives[0];

        $this->assertNull($dive->informationAfterDive?->notes);
    }
}
