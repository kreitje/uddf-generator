<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\ProfileData;

use Kreitje\UddfGenerator\XmlSerializable;

/**
 * <applicationdata> holds vendor-specific extension blocks whose content is
 * arbitrary foreign-namespace XML (<xs:any namespace="##other"
 * processContents="skip">). Rather than modelling unknowable vendor schemas,
 * each block is stored as a list of raw serialized XML fragment strings and
 * passed through byte-for-byte on regeneration.
 */
final class ApplicationData implements XmlSerializable
{
    public function __construct(
        /** @var string[]|null */
        public readonly ?array $decotrainer = null,
        /** @var string[]|null */
        public readonly ?array $hargikas = null,
        /** @var string[]|null */
        public readonly ?array $heinrichsweikamp = null,
        /** @var string[]|null */
        public readonly ?array $tausim = null,
        /** @var string[]|null */
        public readonly ?array $tautabu = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('applicationdata');

        foreach ([
            'decotrainer' => $this->decotrainer,
            'hargikas' => $this->hargikas,
            'heinrichsweikamp' => $this->heinrichsweikamp,
            'tausim' => $this->tausim,
            'tautabu' => $this->tautabu,
        ] as $elementName => $fragments) {
            if ($fragments === null) {
                continue;
            }

            $wrapper = $doc->createElement($elementName);

            foreach ($fragments as $xml) {
                $fragmentDoc = new \DOMDocument();
                $fragmentDoc->loadXML($xml);

                if ($fragmentDoc->documentElement !== null) {
                    $wrapper->appendChild($doc->importNode($fragmentDoc->documentElement, true));
                }
            }

            $el->appendChild($wrapper);
        }

        return $el;
    }
}
