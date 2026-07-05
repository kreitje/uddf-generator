<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator;

use Kreitje\UddfGenerator\Spec\SpecVersionRegistry;

/**
 * Detects the `version` attribute on a UDDF document's root element and
 * delegates to the matching Spec\V{version} parser, falling back to
 * SpecVersionRegistry::LATEST when the declared version isn't registered.
 */
final class UddfParser
{
    public function parse(string $xml): XmlSerializable
    {
        $version = SpecVersionRegistry::detectVersion($xml);
        $parserClass = SpecVersionRegistry::parserClass($version);

        return (new $parserClass())->parse($xml);
    }

    public function parseFile(string $path): XmlSerializable
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
}
