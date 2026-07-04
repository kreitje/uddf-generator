<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Tests;

use Kreitje\UddfGenerator\UddfParser;
use PHPUnit\Framework\TestCase;

final class UddfParserSubsurfaceTest extends TestCase
{
    private UddfParser $parser;

    protected function setUp(): void
    {
        $this->parser = new UddfParser();
    }

    /**
     * Real-world export from Subsurface Divelog. Exercises several edge
     * cases the Maui fixture doesn't: empty-but-present required elements
     * (<firstname/>), self-closing optional numeric elements
     * (<latitude/>), unmodelled sibling sections (<mediadata/>, <buddy>,
     * <divebase>, <tankdata>, <rating>, <divetrip/>), and multiple <link>
     * elements inside <informationbeforedive> that point at different
     * things (a buddy and a dive site).
     */
    private function fixtureXml(): string
    {
        return file_get_contents(__DIR__ . '/fixtures/1.subsurface.uddf.xml');
    }

    public function testParsesWithoutThrowing(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $this->assertInstanceOf(\Kreitje\UddfGenerator\UddfGenerator::class, $uddf);
    }

    public function testGeneratorAndManufacturerProperties(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $this->assertSame('Subsurface Divelog', $uddf->generator->name);
        $this->assertSame('3', $uddf->generator->version);
        $this->assertNull($uddf->generator->datetime);

        $manufacturer = $uddf->generator->manufacturer;
        $this->assertNotNull($manufacturer);
        $this->assertSame('subsurface', $manufacturer->id);
        $this->assertSame('Subsurface Team', $manufacturer->name);
        $this->assertNull($manufacturer->address);
        $this->assertSame(['http://subsurface-divelog.org/'], $manufacturer->contact?->homepages);
        $this->assertSame([], $manufacturer->contact?->emails);
    }

    /**
     * <firstname/> and <lastname/> are present but empty. A required
     * element with empty string content is schema-valid (xs:string allows
     * ''), so this must parse to empty strings rather than throwing.
     */
    public function testOwnerWithEmptyRequiredNameFields(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $owner = $uddf->diver?->owner;
        $this->assertNotNull($owner);
        $this->assertSame('owner', $owner->id);
        $this->assertSame('', $owner->personalData->firstName);
        $this->assertSame('', $owner->personalData->lastName);
        $this->assertNull($owner->personalData->birthdate);
        $this->assertNull($owner->personalData->sex);

        $this->assertNotNull($owner->equipment);
        $this->assertSame([], $owner->equipment->tanks);
    }

    /**
     * <diver><buddy> is not modelled by this library (only <owner> is) and
     * must not affect owner parsing or cause an error.
     */
    public function testBuddyIsIgnored(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $this->assertSame('owner', $uddf->diver?->owner->id);
    }

    /**
     * <divesite><divebase> is not modelled (only <site> is) and must be
     * skipped without affecting site parsing. <latitude/>/<longitude/> are
     * present but empty, which must parse to null rather than 0.0 or
     * throwing.
     */
    public function testDiveSiteWithEmptyOptionalCoordinates(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $this->assertCount(1, $uddf->diveSites);

        $site = $uddf->diveSites[0];
        $this->assertSame('52ad10b4', $site->id);
        $this->assertSame('Houghten Lake', $site->name);
        $this->assertNull($site->notes);

        $geo = $site->geography;
        $this->assertNotNull($geo);
        $this->assertSame('Houghten Lake', $geo->location);
        $this->assertNull($geo->latitude);
        $this->assertNull($geo->longitude);
        $this->assertNull($geo->address);
    }

    /**
     * The mix omits <n2> entirely (only <o2> and <he> are present). The
     * schema allows this (n2 is optional), so it parses to the library's
     * default of 0.0 rather than being derived as 1 - o2 - he.
     */
    public function testGasDefinitionsWithMissingN2(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $mixes = $uddf->gasDefinitions?->mixes ?? [];
        $this->assertCount(1, $mixes);

        $this->assertSame('mix(21/0)', $mixes[0]->id);
        $this->assertSame('air', $mixes[0]->name);
        $this->assertSame(0.21, $mixes[0]->o2);
        $this->assertSame(0.0, $mixes[0]->n2);
        $this->assertSame(0.0, $mixes[0]->he);
    }

    public function testProfileDataStructure(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $groups = $uddf->profileData?->repetitionGroups ?? [];
        $this->assertCount(1, $groups);
        $this->assertSame('idp105553144850559', $groups[0]->id);

        $dives = $groups[0]->dives;
        $this->assertCount(1, $dives);
        $this->assertSame('idp105553144850559', $dives[0]->id);
    }

    /**
     * <informationbeforedive> has two <link> elements: the first refs the
     * buddy, the second refs the dive site. diveSiteRef must resolve to the
     * dive site id ('52ad10b4'), not just the first link encountered.
     */
    public function testInformationBeforeDiveResolvesCorrectLinkAmongMultiple(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $before = $uddf->profileData?->repetitionGroups[0]->dives[0]->informationBeforeDive;

        $this->assertNotNull($before);
        $this->assertSame('2026-07-03T14:26:33', $before->datetime->format('Y-m-d\TH:i:s'));
        $this->assertSame(1, $before->diveNumber);
        $this->assertSame('52ad10b4', $before->diveSiteRef);
    }

    /**
     * <tankdata> is a sibling of <samples> inside <dive> and is not
     * modelled by this library; it must not interfere with waypoint
     * parsing.
     */
    public function testWaypoints(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $waypoints = $uddf->profileData?->repetitionGroups[0]->dives[0]->samples ?? [];
        $this->assertCount(6, $waypoints);

        $expected = [
            [0.0, 0.0],
            [15.0, 100.0],
            [15.0, 2420.0],
            [5.0, 2487.0],
            [5.0, 2667.0],
            [0.0, 2700.0],
        ];

        foreach ($expected as $i => [$depth, $diveTime]) {
            $this->assertSame($depth, $waypoints[$i]->depth, "waypoint {$i} depth");
            $this->assertSame($diveTime, $waypoints[$i]->diveTime, "waypoint {$i} diveTime");
            $this->assertNull($waypoints[$i]->temperature, "waypoint {$i} temperature");
            $this->assertNull($waypoints[$i]->tankPressure, "waypoint {$i} tankPressure");
            $this->assertNull($waypoints[$i]->switchMixRef, "waypoint {$i} switchMixRef");
        }
    }

    /**
     * <rating> and <visibility> siblings are not modelled and must not
     * interfere with parsing the fields that are (greatestdepth,
     * diveduration, averagedepth, notes).
     */
    public function testInformationAfterDive(): void
    {
        $uddf = $this->parser->parse($this->fixtureXml());

        $after = $uddf->profileData?->repetitionGroups[0]->dives[0]->informationAfterDive;

        $this->assertNotNull($after);
        $this->assertSame(15.0, $after->greatestDepth);
        $this->assertSame(2700.0, $after->diveDuration);
        $this->assertSame(13.779, $after->averageDepth);
        $this->assertSame(['This is just a test.'], $after->notes?->paragraphs);
    }
}
