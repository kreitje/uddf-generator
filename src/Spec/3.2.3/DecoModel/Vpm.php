<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DecoModel;

use Kreitje\UddfGenerator\XmlSerializable;

final class Vpm implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        /** @var Tissue[] */
        public readonly array $tissues,
        public readonly ?float $gamma = null,
        public readonly ?float $gc = null,
        public readonly ?float $lambda = null,
        public readonly ?float $r0 = null,
    ) {
        if ($this->tissues === []) {
            throw new \InvalidArgumentException('Vpm requires at least one tissue.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('vpm');
        $el->setAttribute('id', $this->id);

        if ($this->gamma !== null) {
            $el->appendChild($doc->createElement('gamma', (string) $this->gamma));
        }

        if ($this->gc !== null) {
            $el->appendChild($doc->createElement('gc', (string) $this->gc));
        }

        if ($this->lambda !== null) {
            $el->appendChild($doc->createElement('lambda', (string) $this->lambda));
        }

        if ($this->r0 !== null) {
            $el->appendChild($doc->createElement('r0', (string) $this->r0));
        }

        foreach ($this->tissues as $tissue) {
            $el->appendChild($tissue->toXml($doc));
        }

        return $el;
    }
}
