<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\XmlSerializable;

final class Certification implements XmlSerializable
{
    public function __construct(
        public readonly ?string $level = null,
        public readonly ?string $specialty = null,
        public readonly ?string $certificateNumber = null,
        public readonly ?string $organization = null,
        public readonly ?Instructor $instructor = null,
        public readonly ?\DateTimeImmutable $issueDate = null,
        public readonly ?\DateTimeImmutable $validDate = null,
    ) {
        if (($this->level === null) === ($this->specialty === null)) {
            throw new \InvalidArgumentException('Certification must have exactly one of level or specialty.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('certification');

        if ($this->level !== null) {
            $el->appendChild($doc->createElement('level', $this->level));
        } elseif ($this->specialty !== null) {
            $el->appendChild($doc->createElement('specialty', $this->specialty));
        }

        if ($this->certificateNumber !== null) {
            $el->appendChild($doc->createElement('certificatenumber', $this->certificateNumber));
        }

        if ($this->organization !== null) {
            $el->appendChild($doc->createElement('organization', $this->organization));
        }

        if ($this->instructor !== null) {
            $el->appendChild($this->instructor->toXml($doc));
        }

        if ($this->issueDate !== null) {
            $issueDate = $doc->createElement('issuedate');
            $issueDate->appendChild($doc->createElement('datetime', $this->issueDate->format('Y-m-d\TH:i:s')));
            $el->appendChild($issueDate);
        }

        if ($this->validDate !== null) {
            $validDate = $doc->createElement('validdate');
            $validDate->appendChild($doc->createElement('datetime', $this->validDate->format('Y-m-d\TH:i:s')));
            $el->appendChild($validDate);
        }

        return $el;
    }
}
