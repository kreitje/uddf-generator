<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DecoModel;

use Kreitje\UddfGenerator\XmlSerializable;

final class Buehlmann implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        /** @var Tissue[] */
        public readonly array $tissues,
        public readonly ?float $gradientFactorHigh = null,
        public readonly ?float $gradientFactorLow = null,
    ) {
        if ($this->tissues === []) {
            throw new \InvalidArgumentException('Buehlmann requires at least one tissue.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('buehlmann');
        $el->setAttribute('id', $this->id);

        foreach ($this->tissues as $tissue) {
            $el->appendChild($tissue->toXml($doc));
        }

        if ($this->gradientFactorHigh !== null) {
            $el->appendChild($doc->createElement('gradientfactorhigh', (string) $this->gradientFactorHigh));
        }

        if ($this->gradientFactorLow !== null) {
            $el->appendChild($doc->createElement('gradientfactorlow', (string) $this->gradientFactorLow));
        }

        return $el;
    }
}
