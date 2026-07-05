<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\TableGeneration;

use Kreitje\UddfGenerator\ProfileData\ApplicationData;
use Kreitje\UddfGenerator\XmlSerializable;

final class BottomTimeTable implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly BottomTimeTableScope $bottomTimeTableScope,
        public readonly ?string $title = null,
        /** @var string[] */
        public readonly array $linkRefs = [],
        public readonly ?Output $output = null,
        public readonly ?ApplicationData $applicationData = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('bottomtimetable');
        $el->setAttribute('id', $this->id);

        if ($this->title !== null) {
            $el->appendChild($doc->createElement('title', $this->title));
        }

        foreach ($this->linkRefs as $linkRef) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $linkRef);
            $el->appendChild($link);
        }

        if ($this->output !== null) {
            $el->appendChild($this->output->toXml($doc));
        }

        if ($this->applicationData !== null) {
            $el->appendChild($this->applicationData->toXml($doc));
        }

        $el->appendChild($this->bottomTimeTableScope->toXml($doc));

        return $el;
    }
}
