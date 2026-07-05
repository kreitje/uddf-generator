<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveSite;

use Kreitje\UddfGenerator\Spec\V323\Common\Dimension;
use Kreitje\UddfGenerator\Spec\V323\Common\Notes;
use Kreitje\UddfGenerator\XmlSerializable;

final class Wreck implements XmlSerializable
{
    public function __construct(
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?string $shipType = null,
        public readonly ?string $nationality = null,
        public readonly ?string $shipyard = null,
        public readonly ?\DateTimeImmutable $launchingDate = null,
        public readonly ?Dimension $shipDimension = null,
        public readonly ?\DateTimeImmutable $sunk = null,
        public readonly ?Notes $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('wreck');

        $el->appendChild($doc->createElement('name', $this->name));

        foreach ($this->aliasNames as $aliasName) {
            $el->appendChild($doc->createElement('aliasname', $aliasName));
        }

        if ($this->shipType !== null) {
            $el->appendChild($doc->createElement('shiptype', $this->shipType));
        }

        if ($this->nationality !== null) {
            $el->appendChild($doc->createElement('nationality', $this->nationality));
        }

        if ($this->shipyard !== null || $this->launchingDate !== null) {
            $built = $doc->createElement('built');

            if ($this->shipyard !== null) {
                $built->appendChild($doc->createElement('shipyard', $this->shipyard));
            }

            if ($this->launchingDate !== null) {
                $launchingDate = $doc->createElement('launchingdate');
                $launchingDate->appendChild($doc->createElement('datetime', $this->launchingDate->format('Y-m-d\TH:i:s')));
                $built->appendChild($launchingDate);
            }

            $el->appendChild($built);
        }

        if ($this->shipDimension !== null) {
            $el->appendChild($this->shipDimension->toXml($doc));
        }

        if ($this->sunk !== null) {
            $sunk = $doc->createElement('sunk');
            $sunk->appendChild($doc->createElement('datetime', $this->sunk->format('Y-m-d\TH:i:s')));
            $el->appendChild($sunk);
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
