<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Diver;

use Kreitje\UddfGenerator\XmlSerializable;

final class Membership implements XmlSerializable
{
    public function __construct(
        public readonly string $organisation,
        public readonly ?string $memberId = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('membership');
        $el->setAttribute('organisation', $this->organisation);

        if ($this->memberId !== null) {
            $el->setAttribute('memberid', $this->memberId);
        }

        return $el;
    }
}
