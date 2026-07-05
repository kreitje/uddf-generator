<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\ProfileData;

use Kreitje\UddfGenerator\XmlSerializable;

final class ProfileData implements XmlSerializable
{
    public function __construct(
        /** @var RepetitionGroup[] */
        public readonly array $repetitionGroups,
    ) {
        if ($this->repetitionGroups === []) {
            throw new \InvalidArgumentException('ProfileData must contain at least one repetition group.');
        }
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('profiledata');

        foreach ($this->repetitionGroups as $group) {
            $el->appendChild($group->toXml($doc));
        }

        return $el;
    }
}
