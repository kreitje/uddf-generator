<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Diver;

use Kreitje\UddfGenerator\Spec\V323\Enum\Sex;
use Kreitje\UddfGenerator\Spec\V323\Enum\Smoking;
use Kreitje\UddfGenerator\XmlSerializable;

final class PersonalData implements XmlSerializable
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $middleName = null,
        public readonly ?string $birthName = null,
        public readonly ?string $honorific = null,
        public readonly ?Sex $sex = null,
        public readonly ?\DateTimeImmutable $birthdate = null,
        public readonly ?string $passport = null,
        public readonly ?Membership $membership = null,
        public readonly ?float $height = null,
        public readonly ?float $weight = null,
        public readonly ?string $bloodGroup = null,
        public readonly ?Smoking $smoking = null,
        public readonly ?NumberOfDives $numberOfDives = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('personal');

        $el->appendChild($doc->createElement('firstname', $this->firstName));

        if ($this->middleName !== null) {
            $el->appendChild($doc->createElement('middlename', $this->middleName));
        }

        $el->appendChild($doc->createElement('lastname', $this->lastName));

        if ($this->birthName !== null) {
            $el->appendChild($doc->createElement('birthname', $this->birthName));
        }

        if ($this->honorific !== null) {
            $el->appendChild($doc->createElement('honorific', $this->honorific));
        }

        if ($this->sex !== null) {
            $el->appendChild($doc->createElement('sex', $this->sex->value));
        }

        if ($this->birthdate !== null) {
            $birthdateEl = $doc->createElement('birthdate');
            $birthdateEl->appendChild($doc->createElement('datetime', $this->birthdate->format('Y-m-d\TH:i:s')));
            $el->appendChild($birthdateEl);
        }

        if ($this->passport !== null) {
            $el->appendChild($doc->createElement('passport', $this->passport));
        }

        if ($this->membership !== null) {
            $el->appendChild($this->membership->toXml($doc));
        }

        if ($this->height !== null) {
            $el->appendChild($doc->createElement('height', (string) $this->height));
        }

        if ($this->weight !== null) {
            $el->appendChild($doc->createElement('weight', (string) $this->weight));
        }

        if ($this->bloodGroup !== null) {
            $el->appendChild($doc->createElement('bloodgroup', $this->bloodGroup));
        }

        if ($this->smoking !== null) {
            $el->appendChild($doc->createElement('smoking', $this->smoking->value));
        }

        if ($this->numberOfDives !== null) {
            $el->appendChild($this->numberOfDives->toXml($doc));
        }

        return $el;
    }
}
