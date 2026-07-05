<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveTrip;

use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\Common\Price;
use Kreitje\UddfGenerator\Common\PricePerDivePackage;
use Kreitje\UddfGenerator\Common\Rating;
use Kreitje\UddfGenerator\DiveSite\Geography;
use Kreitje\UddfGenerator\XmlSerializable;

final class TripPart implements XmlSerializable
{
    public function __construct(
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?string $type = null,
        public readonly ?DateOfTrip $dateOfTrip = null,
        public readonly ?Geography $geography = null,
        public readonly ?Accommodation $accommodation = null,
        public readonly ?Operator $operator = null,
        public readonly ?Vessel $vessel = null,
        public readonly ?string $linkRef = null,
        /** @var string[] */
        public readonly array $relatedDiveRefs = [],
        public readonly ?PricePerDivePackage $priceDivePackage = null,
        public readonly ?Price $pricePerDive = null,
        /** @var Rating[] */
        public readonly array $ratings = [],
        public readonly ?Notes $notes = null,
    ) {
        if (($this->operator !== null) !== ($this->vessel !== null)) {
            throw new \InvalidArgumentException('TripPart operator and vessel must be set together.');
        }

        if ($this->accommodation !== null && $this->operator !== null) {
            throw new \InvalidArgumentException('TripPart cannot have both accommodation and operator/vessel.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('trippart');

        if ($this->type !== null) {
            $el->setAttribute('type', $this->type);
        }

        $el->appendChild($doc->createElement('name', $this->name));

        foreach ($this->aliasNames as $aliasName) {
            $el->appendChild($doc->createElement('aliasname', $aliasName));
        }

        if ($this->dateOfTrip !== null) {
            $el->appendChild($this->dateOfTrip->toXml($doc));
        }

        if ($this->geography !== null) {
            $el->appendChild($this->geography->toXml($doc));
        }

        if ($this->accommodation !== null) {
            $el->appendChild($this->accommodation->toXml($doc));
        } elseif ($this->operator !== null && $this->vessel !== null) {
            $el->appendChild($this->operator->toXml($doc));
            $el->appendChild($this->vessel->toXml($doc));
        }

        if ($this->linkRef !== null) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $this->linkRef);
            $el->appendChild($link);
        }

        if ($this->relatedDiveRefs !== []) {
            $relatedDives = $doc->createElement('relateddives');
            foreach ($this->relatedDiveRefs as $ref) {
                $link = $doc->createElement('link');
                $link->setAttribute('ref', $ref);
                $relatedDives->appendChild($link);
            }
            $el->appendChild($relatedDives);
        }

        if ($this->priceDivePackage !== null) {
            $el->appendChild($this->priceDivePackage->toXml($doc));
        }

        if ($this->pricePerDive !== null) {
            $el->appendChild($this->pricePerDive->toXml($doc, 'priceperdive'));
        }

        foreach ($this->ratings as $rating) {
            $el->appendChild($rating->toXml($doc));
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
