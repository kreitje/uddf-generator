<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Tests;

use Kreitje\UddfGenerator\Spec\SpecVersionRegistry;
use Kreitje\UddfGenerator\UddfGenerator;
use Kreitje\UddfGenerator\UddfParser;
use PHPUnit\Framework\TestCase;

final class SpecVersionRegistryTest extends TestCase
{
    public function testDetectVersionReadsRootAttribute(): void
    {
        $xml = '<uddf version="3.2.3"><generator><name>x</name></generator></uddf>';

        $this->assertSame('3.2.3', SpecVersionRegistry::detectVersion($xml));
    }

    public function testDetectVersionReturnsNullWhenAbsent(): void
    {
        $xml = '<uddf><generator><name>x</name></generator></uddf>';

        $this->assertNull(SpecVersionRegistry::detectVersion($xml));
    }

    public function testDetectVersionReturnsNullOnMalformedXml(): void
    {
        $this->assertNull(SpecVersionRegistry::detectVersion('<not valid'));
    }

    public function testIsSupported(): void
    {
        $this->assertTrue(SpecVersionRegistry::isSupported('3.2.3'));
        $this->assertFalse(SpecVersionRegistry::isSupported('3.2.1'));
    }

    public function testUnregisteredVersionFallsBackToLatest(): void
    {
        $this->assertSame(
            \Kreitje\UddfGenerator\Spec\V323\UddfParser::class,
            SpecVersionRegistry::parserClass('3.2.1'),
        );
        $this->assertSame(
            \Kreitje\UddfGenerator\Spec\V323\UddfGenerator::class,
            SpecVersionRegistry::generatorClass('9.9.9'),
        );
    }

    public function testTopLevelUddfGeneratorIsAnAliasOfLatestVersion(): void
    {
        $this->assertSame(\Kreitje\UddfGenerator\Spec\V323\UddfGenerator::class, (new \ReflectionClass(UddfGenerator::class))->getName());
    }

    public function testParsingDocumentWithUnregisteredVersionFallsBackRatherThanThrowing(): void
    {
        $xml = '<uddf version="3.2.1"><generator><name>Old Doc</name></generator></uddf>';

        $parsed = (new UddfParser())->parse($xml);

        $this->assertSame('Old Doc', $parsed->generator->name);
    }

    public function testVersionedNamespaceIsUsableDirectly(): void
    {
        $generator = new \Kreitje\UddfGenerator\Spec\V323\UddfGenerator(
            generator: new \Kreitje\UddfGenerator\Spec\V323\Generator\Generator(name: 'Direct', version: '1.0'),
        );

        $this->assertStringContainsString('Direct', $generator->generate());
    }
}
