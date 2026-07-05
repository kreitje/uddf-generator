<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveSite;

use Kreitje\UddfGenerator\Common\Address;
use Kreitje\UddfGenerator\Common\Contact;
use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\Common\Price;
use Kreitje\UddfGenerator\Common\PricePerDivePackage;
use Kreitje\UddfGenerator\Common\Rating;
use Kreitje\UddfGenerator\XmlSerializable;

final class Divebase implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?Address $address = null,
        public readonly ?Contact $contact = null,
        public readonly ?Price $pricePerDive = null,
        public readonly ?PricePerDivePackage $priceDivePackage = null,
        /** @var Guide[] */
        public readonly array $guides = [],
        /** @var Rating[] */
        public readonly array $ratings = [],
        public readonly ?string $linkRef = null,
        public readonly ?Notes $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('divebase');
        $el->setAttribute('id', $this->id);

        $el->appendChild($doc->createElement('name', $this->name));

        foreach ($this->aliasNames as $aliasName) {
            $el->appendChild($doc->createElement('aliasname', $aliasName));
        }

        if ($this->address !== null) {
            $el->appendChild($this->address->toXml($doc));
        }

        if ($this->contact !== null) {
            $el->appendChild($this->contact->toXml($doc));
        }

        if ($this->pricePerDive !== null) {
            $el->appendChild($this->pricePerDive->toXml($doc, 'priceperdive'));
        }

        if ($this->priceDivePackage !== null) {
            $el->appendChild($this->priceDivePackage->toXml($doc));
        }

        foreach ($this->guides as $guide) {
            $el->appendChild($guide->toXml($doc));
        }

        foreach ($this->ratings as $rating) {
            $el->appendChild($rating->toXml($doc));
        }

        if ($this->linkRef !== null) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $this->linkRef);
            $el->appendChild($link);
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
