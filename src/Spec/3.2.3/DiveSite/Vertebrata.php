<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveSite;

use Kreitje\UddfGenerator\XmlSerializable;

final class Vertebrata implements XmlSerializable
{
    public function __construct(
        public readonly ?SingleLifeForm $chondrichthyes = null,
        public readonly ?SingleLifeForm $osteichthyes = null,
        public readonly ?SingleLifeForm $mammalia = null,
        public readonly ?SingleLifeForm $amphibia = null,
        public readonly ?SingleLifeForm $reptilia = null,
        public readonly ?SingleLifeForm $vertebrataVarious = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('vertebrata');

        foreach ([
            'chondrichthyes' => $this->chondrichthyes,
            'osteichthyes' => $this->osteichthyes,
            'mammalia' => $this->mammalia,
            'amphibia' => $this->amphibia,
            'reptilia' => $this->reptilia,
            'vertebratavarious' => $this->vertebrataVarious,
        ] as $elementName => $lifeForm) {
            if ($lifeForm !== null) {
                $el->appendChild($lifeForm->toXml($doc, $elementName));
            }
        }

        return $el;
    }
}
