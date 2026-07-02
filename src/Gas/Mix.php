<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Gas;

use Kreitje\UddfGenerator\XmlSerializable;

final class Mix implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly float $o2,
        public readonly float $n2,
        public readonly float $he = 0.0,
    ) {}

    public static function air(string $id = 'air'): self
    {
        return new self(id: $id, name: 'Air', o2: 0.21, n2: 0.79);
    }

    public static function nitrox(string $id, float $o2Fraction): self
    {
        if ($o2Fraction <= 0.0 || $o2Fraction > 1.0) {
            throw new \InvalidArgumentException('O2 fraction must be between 0 and 1.');
        }

        return new self(id: $id, name: sprintf('EAN%d', (int) round($o2Fraction * 100)), o2: $o2Fraction, n2: 1.0 - $o2Fraction);
    }

    public static function trimix(string $id, float $o2Fraction, float $heFraction): self
    {
        if ($o2Fraction + $heFraction > 1.0) {
            throw new \InvalidArgumentException('O2 and He fractions must sum to 1 or less.');
        }

        return new self(
            id: $id,
            name: sprintf('Tx%d/%d', (int) round($o2Fraction * 100), (int) round($heFraction * 100)),
            o2: $o2Fraction,
            n2: 1.0 - $o2Fraction - $heFraction,
            he: $heFraction,
        );
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('mix');
        $el->setAttribute('id', $this->id);

        $el->appendChild($doc->createElement('name', $this->name));
        $el->appendChild($doc->createElement('o2', (string) $this->o2));
        $el->appendChild($doc->createElement('n2', (string) $this->n2));
        $el->appendChild($doc->createElement('he', (string) $this->he));

        return $el;
    }
}
