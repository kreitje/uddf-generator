<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Media;

use Kreitje\UddfGenerator\XmlSerializable;

final class Image implements XmlSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $objectName,
        public readonly ?string $title = null,
        public readonly ?ImageData $imageData = null,
        public readonly ?int $height = null,
        public readonly ?int $width = null,
        public readonly ?string $format = null,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $media = new Media(id: $this->id, objectName: $this->objectName, title: $this->title);
        $el = $media->toXml($doc, 'image');

        if ($this->imageData !== null) {
            $el->appendChild($this->imageData->toXml($doc));
        }

        if ($this->height !== null) {
            $el->setAttribute('height', (string) $this->height);
        }

        if ($this->width !== null) {
            $el->setAttribute('width', (string) $this->width);
        }

        if ($this->format !== null) {
            $el->setAttribute('format', $this->format);
        }

        return $el;
    }
}
