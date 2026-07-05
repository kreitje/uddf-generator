<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Diver;

use Kreitje\UddfGenerator\Common\Address;
use Kreitje\UddfGenerator\Common\Contact;
use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\XmlSerializable;

final class Owner implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly PersonalData $personalData,
        public readonly ?Address $address = null,
        public readonly ?Contact $contact = null,
        public readonly ?Equipment $equipment = null,
        /** @var Examination[] */
        public readonly array $medicalExaminations = [],
        /** @var Certification[] */
        public readonly array $certifications = [],
        /** @var Permit[] */
        public readonly array $divePermits = [],
        /** @var Insurance[] */
        public readonly array $diveInsurances = [],
        public readonly ?Notes $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('owner');
        $el->setAttribute('id', $this->id);

        $el->appendChild($this->personalData->toXml($doc));

        if ($this->address !== null) {
            $el->appendChild($this->address->toXml($doc));
        }

        if ($this->contact !== null) {
            $el->appendChild($this->contact->toXml($doc));
        }

        if ($this->equipment !== null) {
            $el->appendChild($this->equipment->toXml($doc));
        }

        if ($this->medicalExaminations !== []) {
            $medical = $doc->createElement('medical');
            foreach ($this->medicalExaminations as $examination) {
                $medical->appendChild($examination->toXml($doc));
            }
            $el->appendChild($medical);
        }

        if ($this->certifications !== []) {
            $education = $doc->createElement('education');
            foreach ($this->certifications as $certification) {
                $education->appendChild($certification->toXml($doc));
            }
            $el->appendChild($education);
        }

        if ($this->divePermits !== []) {
            $divePermissions = $doc->createElement('divepermissions');
            foreach ($this->divePermits as $permit) {
                $divePermissions->appendChild($permit->toXml($doc));
            }
            $el->appendChild($divePermissions);
        }

        if ($this->diveInsurances !== []) {
            $diveInsurances = $doc->createElement('diveinsurances');
            foreach ($this->diveInsurances as $insurance) {
                $diveInsurances->appendChild($insurance->toXml($doc));
            }
            $el->appendChild($diveInsurances);
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
