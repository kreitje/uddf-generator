<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveSite;

use Kreitje\UddfGenerator\Spec\V323\Common\Notes;
use Kreitje\UddfGenerator\Spec\V323\Common\Rating;
use Kreitje\UddfGenerator\Spec\V323\Enum\Environment;
use Kreitje\UddfGenerator\XmlSerializable;

final class DiveSite implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?Environment $environment = null,
        public readonly ?Geography $geography = null,
        public readonly ?Ecology $ecology = null,
        public readonly ?SiteData $siteData = null,
        /** @var Rating[] */
        public readonly array $ratings = [],
        public readonly ?Notes $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('site');
        $el->setAttribute('id', $this->id);

        $el->appendChild($doc->createElement('name', $this->name));

        foreach ($this->aliasNames as $aliasName) {
            $el->appendChild($doc->createElement('aliasname', $aliasName));
        }

        if ($this->environment !== null) {
            $el->appendChild($doc->createElement('environment', $this->environment->value));
        }

        if ($this->geography !== null) {
            $el->appendChild($this->geography->toXml($doc));
        }

        if ($this->ecology !== null) {
            $el->appendChild($this->ecology->toXml($doc));
        }

        if ($this->siteData !== null) {
            $el->appendChild($this->siteData->toXml($doc));
        }

        foreach ($this->ratings as $rating) {
            $el->appendChild($rating->toXml($doc));
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
