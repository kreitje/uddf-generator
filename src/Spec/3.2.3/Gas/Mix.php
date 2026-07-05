<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Gas;

use Kreitje\UddfGenerator\Spec\V323\Common\Price;
use Kreitje\UddfGenerator\XmlSerializable;

final class Mix implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly float $o2,
        public readonly float $n2,
        public readonly float $he = 0.0,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?float $ar = null,
        public readonly ?float $h2 = null,
        public readonly ?Price $pricePerLitre = null,
        public readonly ?float $maximumPo2 = null,
        public readonly ?float $maximumOperationDepth = null,
        public readonly ?float $equivalentAirDepth = null,
    ) {
        if ($this->maximumPo2 !== null && $this->maximumOperationDepth !== null) {
            throw new \InvalidArgumentException('A mix cannot have both maximumPo2 and maximumOperationDepth.');
        }
    }

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

        foreach ($this->aliasNames as $aliasName) {
            $el->appendChild($doc->createElement('aliasname', $aliasName));
        }

        $el->appendChild($doc->createElement('o2', (string) $this->o2));
        $el->appendChild($doc->createElement('n2', (string) $this->n2));
        $el->appendChild($doc->createElement('he', (string) $this->he));

        if ($this->ar !== null) {
            $el->appendChild($doc->createElement('ar', (string) $this->ar));
        }

        if ($this->h2 !== null) {
            $el->appendChild($doc->createElement('h2', (string) $this->h2));
        }

        if ($this->pricePerLitre !== null) {
            $el->appendChild($this->pricePerLitre->toXml($doc, 'priceperlitre'));
        }

        if ($this->maximumPo2 !== null) {
            $el->appendChild($doc->createElement('maximumpo2', (string) $this->maximumPo2));
        } elseif ($this->maximumOperationDepth !== null) {
            $el->appendChild($doc->createElement('maximumoperationdepth', (string) $this->maximumOperationDepth));
        }

        if ($this->equivalentAirDepth !== null) {
            $el->appendChild($doc->createElement('equivalentairdepth', (string) $this->equivalentAirDepth));
        }

        return $el;
    }
}
