<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\ProfileData;

use Kreitje\UddfGenerator\Spec\V323\Common\Notes;

final class Drug
{
    public function __construct(
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?bool $periodicallyTaken = null,
        public readonly ?float $timeSpanBeforeDive = null,
        public readonly ?Notes $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc, string $elementName): \DOMElement
    {
        $el = $doc->createElement($elementName);

        $el->appendChild($doc->createElement('name', $this->name));

        foreach ($this->aliasNames as $aliasName) {
            $el->appendChild($doc->createElement('aliasname', $aliasName));
        }

        if ($this->periodicallyTaken !== null) {
            $el->appendChild($doc->createElement('periodicallytaken', $this->periodicallyTaken ? 'yes' : 'no'));
        }

        if ($this->timeSpanBeforeDive !== null) {
            $el->appendChild($doc->createElement('timespanbeforedive', (string) $this->timeSpanBeforeDive));
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
