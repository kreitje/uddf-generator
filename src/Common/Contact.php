<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Common;

use Kreitje\UddfGenerator\XmlSerializable;

final class Contact implements XmlSerializable
{
    public function __construct(
        /** @var string[] */
        public readonly array $languages = [],
        /** @var string[] */
        public readonly array $phones = [],
        /** @var string[] */
        public readonly array $mobilePhones = [],
        /** @var string[] */
        public readonly array $faxes = [],
        /** @var string[] */
        public readonly array $emails = [],
        /** @var string[] */
        public readonly array $homepages = [],
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('contact');

        foreach ($this->languages as $language) {
            $el->appendChild($doc->createElement('language', $language));
        }

        foreach ($this->phones as $phone) {
            $el->appendChild($doc->createElement('phone', $phone));
        }

        foreach ($this->mobilePhones as $mobilePhone) {
            $el->appendChild($doc->createElement('mobilephone', $mobilePhone));
        }

        foreach ($this->faxes as $fax) {
            $el->appendChild($doc->createElement('fax', $fax));
        }

        foreach ($this->emails as $email) {
            $el->appendChild($doc->createElement('email', $email));
        }

        foreach ($this->homepages as $homepage) {
            $el->appendChild($doc->createElement('homepage', $homepage));
        }

        return $el;
    }
}
