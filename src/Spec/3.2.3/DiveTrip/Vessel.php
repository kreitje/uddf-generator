<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveTrip;

use Kreitje\UddfGenerator\Spec\V323\Common\Address;
use Kreitje\UddfGenerator\Spec\V323\Common\Contact;
use Kreitje\UddfGenerator\Spec\V323\Common\Dimension;
use Kreitje\UddfGenerator\Spec\V323\Common\Notes;
use Kreitje\UddfGenerator\Spec\V323\Common\Rating;
use Kreitje\UddfGenerator\XmlSerializable;

final class Vessel implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?string $shipType = null,
        public readonly ?string $marina = null,
        public readonly ?Address $address = null,
        public readonly ?Contact $contact = null,
        public readonly ?Dimension $shipDimension = null,
        /** @var Rating[] */
        public readonly array $ratings = [],
        public readonly ?Notes $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('vessel');
        $el->setAttribute('id', $this->id);

        $el->appendChild($doc->createElement('name', $this->name));

        foreach ($this->aliasNames as $aliasName) {
            $el->appendChild($doc->createElement('aliasname', $aliasName));
        }

        if ($this->shipType !== null) {
            $el->appendChild($doc->createElement('shiptype', $this->shipType));
        }

        if ($this->marina !== null) {
            $el->appendChild($doc->createElement('marina', $this->marina));
        }

        if ($this->address !== null) {
            $el->appendChild($this->address->toXml($doc));
        }

        if ($this->contact !== null) {
            $el->appendChild($this->contact->toXml($doc));
        }

        if ($this->shipDimension !== null) {
            $el->appendChild($this->shipDimension->toXml($doc));
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
