<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec;

final class SpecVersionRegistry
{
    public const LATEST = '3.2.3';

    /** @var array<string, array{parser: class-string, generator: class-string}> */
    private const VERSIONS = [
        '3.2.3' => [
            'parser' => \Kreitje\UddfGenerator\Spec\V323\UddfParser::class,
            'generator' => \Kreitje\UddfGenerator\Spec\V323\UddfGenerator::class,
        ],
    ];

    /**
     * Reads the `version` attribute off the root element without fully
     * validating the document — the resolved parser still does that. Returns
     * null on malformed XML or a missing/empty version attribute, in which
     * case callers fall back to the latest known version.
     */
    public static function detectVersion(string $xml): ?string
    {
        $prev = libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $loaded = $doc->loadXML($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        if (!$loaded || $doc->documentElement === null) {
            return null;
        }

        $version = $doc->documentElement->getAttribute('version');

        return $version !== '' ? $version : null;
    }

    public static function isSupported(string $version): bool
    {
        return isset(self::VERSIONS[$version]);
    }

    /** @return class-string */
    public static function parserClass(?string $version): string
    {
        return self::VERSIONS[$version]['parser'] ?? self::VERSIONS[self::LATEST]['parser'];
    }

    /** @return class-string */
    public static function generatorClass(?string $version = null): string
    {
        return self::VERSIONS[$version]['generator'] ?? self::VERSIONS[self::LATEST]['generator'];
    }
}
