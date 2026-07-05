<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\DiveSite;

use Kreitje\UddfGenerator\XmlSerializable;

final class Invertebrata implements XmlSerializable
{
    public function __construct(
        public readonly ?SingleLifeForm $porifera = null,
        public readonly ?SingleLifeForm $coelenterata = null,
        public readonly ?SingleLifeForm $cnidaria = null,
        public readonly ?SingleLifeForm $ctenophora = null,
        public readonly ?SingleLifeForm $plathelminthes = null,
        public readonly ?SingleLifeForm $bryozoa = null,
        public readonly ?SingleLifeForm $phoronidea = null,
        public readonly ?SingleLifeForm $ascidiacea = null,
        public readonly ?SingleLifeForm $echinodermata = null,
        public readonly ?SingleLifeForm $mollusca = null,
        public readonly ?SingleLifeForm $crustacea = null,
        public readonly ?SingleLifeForm $invertebrataVarious = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('invertebrata');

        foreach ([
            'porifera' => $this->porifera,
            'coelenterata' => $this->coelenterata,
            'cnidaria' => $this->cnidaria,
            'ctenophora' => $this->ctenophora,
            'plathelminthes' => $this->plathelminthes,
            'bryozoa' => $this->bryozoa,
            'phoronidea' => $this->phoronidea,
            'ascidiacea' => $this->ascidiacea,
            'echinodermata' => $this->echinodermata,
            'mollusca' => $this->mollusca,
            'crustacea' => $this->crustacea,
            'invertebratavarious' => $this->invertebrataVarious,
        ] as $elementName => $lifeForm) {
            if ($lifeForm !== null) {
                $el->appendChild($lifeForm->toXml($doc, $elementName));
            }
        }

        return $el;
    }
}
