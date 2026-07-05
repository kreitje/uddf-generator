<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveComputerControl;

use Kreitje\UddfGenerator\ProfileData\ApplicationData;
use Kreitje\UddfGenerator\XmlSerializable;

final class DcDecoModel implements XmlSerializable
{
    public function __construct(
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?ApplicationData $applicationData = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('setdcdecomodel');

        $el->appendChild($doc->createElement('name', $this->name));

        foreach ($this->aliasNames as $aliasName) {
            $el->appendChild($doc->createElement('aliasname', $aliasName));
        }

        if ($this->applicationData !== null) {
            $el->appendChild($this->applicationData->toXml($doc));
        }

        return $el;
    }
}
