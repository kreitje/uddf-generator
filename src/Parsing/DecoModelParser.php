<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Parsing;

use Kreitje\UddfGenerator\DecoModel\Buehlmann;
use Kreitje\UddfGenerator\DecoModel\DecoModel;
use Kreitje\UddfGenerator\DecoModel\Rgbm;
use Kreitje\UddfGenerator\DecoModel\Tissue;
use Kreitje\UddfGenerator\DecoModel\Vpm;
use Kreitje\UddfGenerator\Enum\TissueGas;

final class DecoModelParser
{
    use DomHelpers;

    public function parse(\DOMElement $root): ?DecoModel
    {
        $el = $this->child($root, 'decomodel');

        if ($el === null) {
            return null;
        }

        $buehlmannEl = $this->child($el, 'buehlmann');
        $rgbmEl = $this->child($el, 'rgbm');
        $vpmEl = $this->child($el, 'vpm');

        if ($buehlmannEl === null || $rgbmEl === null || $vpmEl === null) {
            return null;
        }

        return new DecoModel(
            buehlmann: new Buehlmann(
                id: $buehlmannEl->getAttribute('id'),
                tissues: $this->parseTissues($buehlmannEl),
                gradientFactorHigh: $this->float($buehlmannEl, 'gradientfactorhigh'),
                gradientFactorLow: $this->float($buehlmannEl, 'gradientfactorlow'),
            ),
            rgbm: new Rgbm(
                id: $rgbmEl->getAttribute('id'),
                tissues: $this->parseTissues($rgbmEl),
            ),
            vpm: new Vpm(
                id: $vpmEl->getAttribute('id'),
                tissues: $this->parseTissues($vpmEl),
                gamma: $this->float($vpmEl, 'gamma'),
                gc: $this->float($vpmEl, 'gc'),
                lambda: $this->float($vpmEl, 'lambda'),
                r0: $this->float($vpmEl, 'r0'),
            ),
        );
    }

    /** @return Tissue[] */
    private function parseTissues(\DOMElement $parent): array
    {
        return array_map(
            fn (\DOMElement $el): Tissue => new Tissue(
                gas: $this->parseEnumAttr(TissueGas::class, $el, 'gas') ?? TissueGas::N2,
                number: $this->attrInt($el, 'number') ?? 0,
                halfLife: $this->attrFloat($el, 'halflife') ?? 0.0,
                a: $this->attrFloat($el, 'a') ?? 0.0,
                b: $this->attrFloat($el, 'b') ?? 0.0,
            ),
            $this->children($parent, 'tissue'),
        );
    }
}
