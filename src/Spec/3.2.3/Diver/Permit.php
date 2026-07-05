<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Diver;

use Kreitje\UddfGenerator\Spec\V323\Common\Notes;
use Kreitje\UddfGenerator\XmlSerializable;

final class Permit implements XmlSerializable
{
    public function __construct(
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?string $region = null,
        public readonly ?\DateTimeImmutable $issueDate = null,
        public readonly ?\DateTimeImmutable $validDate = null,
        public readonly ?Notes $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('permit');

        $el->appendChild($doc->createElement('name', $this->name));

        foreach ($this->aliasNames as $aliasName) {
            $el->appendChild($doc->createElement('aliasname', $aliasName));
        }

        if ($this->region !== null) {
            $el->appendChild($doc->createElement('region', $this->region));
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

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
