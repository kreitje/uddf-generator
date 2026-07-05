<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Media;

use Kreitje\UddfGenerator\XmlSerializable;

final class MediaData implements XmlSerializable
{
    public function __construct(
        /** @var Media[] */
        public readonly array $audio = [],
        /** @var Image[] */
        public readonly array $images = [],
        /** @var Media[] */
        public readonly array $video = [],
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('mediadata');

        foreach ($this->audio as $audio) {
            $el->appendChild($audio->toXml($doc, 'audio'));
        }

        foreach ($this->images as $image) {
            $el->appendChild($image->toXml($doc));
        }

        foreach ($this->video as $video) {
            $el->appendChild($video->toXml($doc, 'video'));
        }

        return $el;
    }
}
