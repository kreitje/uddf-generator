<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\XmlSerializable;

final class PersonalData implements XmlSerializable
{
    public function __construct(
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?\DateTimeImmutable $birthdate = null,
        public readonly ?string $sex = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('personal');

        if ($this->firstName !== null) {
            $el->appendChild($doc->createElement('firstname', $this->firstName));
        }

        if ($this->lastName !== null) {
            $el->appendChild($doc->createElement('lastname', $this->lastName));
        }

        if ($this->birthdate !== null) {
            $el->appendChild($doc->createElement('birthdate', $this->birthdate->format('Y-m-d')));
        }

        if ($this->sex !== null) {
            $el->appendChild($doc->createElement('sex', $this->sex));
        }

        return $el;
    }
}
