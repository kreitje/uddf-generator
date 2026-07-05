<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveSite;

use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\XmlSerializable;

final class Flora implements XmlSerializable
{
    public function __construct(
        public readonly ?SingleLifeForm $rhodophyceae = null,
        public readonly ?SingleLifeForm $phaeophyceae = null,
        public readonly ?SingleLifeForm $chlorophyceae = null,
        public readonly ?SingleLifeForm $spermatophyta = null,
        public readonly ?SingleLifeForm $floraVarious = null,
        public readonly ?Notes $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('flora');

        foreach ([
            'rhodophyceae' => $this->rhodophyceae,
            'phaeophyceae' => $this->phaeophyceae,
            'chlorophyceae' => $this->chlorophyceae,
            'spermatophyta' => $this->spermatophyta,
            'floravarious' => $this->floraVarious,
        ] as $elementName => $lifeForm) {
            if ($lifeForm !== null) {
                $el->appendChild($lifeForm->toXml($doc, $elementName));
            }
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
