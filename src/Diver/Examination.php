<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\Enum\ExaminationResult;
use Kreitje\UddfGenerator\XmlSerializable;

final class Examination implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly ?\DateTimeImmutable $datetime = null,
        public readonly ?Doctor $doctor = null,
        public readonly ?string $doctorRef = null,
        public readonly ?ExaminationResult $result = null,
        public readonly ?float $totalLungCapacity = null,
        public readonly ?float $vitalCapacity = null,
        public readonly ?Notes $notes = null,
    ) {
        if ($this->doctor !== null && $this->doctorRef !== null) {
            throw new \InvalidArgumentException('Examination cannot have both an inline doctor and a doctor link reference.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('examination');
        $el->setAttribute('id', $this->id);

        if ($this->datetime !== null) {
            $el->appendChild($doc->createElement('datetime', $this->datetime->format('Y-m-d\TH:i:s')));
        }

        if ($this->doctor !== null) {
            $el->appendChild($this->doctor->toXml($doc));
        } elseif ($this->doctorRef !== null) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $this->doctorRef);
            $el->appendChild($link);
        }

        if ($this->result !== null) {
            $el->appendChild($doc->createElement('examinationresult', $this->result->value));
        }

        if ($this->totalLungCapacity !== null) {
            $el->appendChild($doc->createElement('totallungcapacity', (string) $this->totalLungCapacity));
        }

        if ($this->vitalCapacity !== null) {
            $el->appendChild($doc->createElement('vitalcapacity', (string) $this->vitalCapacity));
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
