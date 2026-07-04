<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\XmlSerializable;

final class PersonalData implements XmlSerializable
{
    private const VALID_SEXES = ['undetermined', 'male', 'female', 'hermaphrodite'];

    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?\DateTimeImmutable $birthdate = null,
        public readonly ?string $sex = null,
    ) {
        if ($this->sex !== null && !in_array($this->sex, self::VALID_SEXES, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid sex "%s"; must be one of: %s.',
                $this->sex,
                implode(', ', self::VALID_SEXES),
            ));
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('personal');

        $el->appendChild($doc->createElement('firstname', $this->firstName));
        $el->appendChild($doc->createElement('lastname', $this->lastName));

        if ($this->sex !== null) {
            $el->appendChild($doc->createElement('sex', $this->sex));
        }

        if ($this->birthdate !== null) {
            $birthdateEl = $doc->createElement('birthdate');
            $birthdateEl->appendChild($doc->createElement('datetime', $this->birthdate->format('Y-m-d\TH:i:s')));
            $el->appendChild($birthdateEl);
        }

        return $el;
    }
}
