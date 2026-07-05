<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Diver;

use Kreitje\UddfGenerator\Spec\V323\Common\Notes;
use Kreitje\UddfGenerator\Spec\V323\Generator\Manufacturer;

final class EquipmentPiece
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        /** @var string[] */
        public readonly array $aliasNames = [],
        public readonly ?string $linkRef = null,
        public readonly ?Manufacturer $manufacturer = null,
        public readonly ?string $model = null,
        public readonly ?string $serialNumber = null,
        public readonly ?Purchase $purchase = null,
        public readonly ?int $serviceInterval = null,
        public readonly ?\DateTimeImmutable $nextServiceDate = null,
        public readonly ?Notes $notes = null,
    ) {}

    public function toXml(\DOMDocument $doc, string $elementName): \DOMElement
    {
        $el = $doc->createElement($elementName);
        $el->setAttribute('id', $this->id);

        $el->appendChild($doc->createElement('name', $this->name));

        foreach ($this->aliasNames as $aliasName) {
            $el->appendChild($doc->createElement('aliasname', $aliasName));
        }

        if ($this->linkRef !== null) {
            $link = $doc->createElement('link');
            $link->setAttribute('ref', $this->linkRef);
            $el->appendChild($link);
        }

        if ($this->manufacturer !== null) {
            $el->appendChild($this->manufacturer->toXml($doc));
        }

        if ($this->model !== null) {
            $el->appendChild($doc->createElement('model', $this->model));
        }

        if ($this->serialNumber !== null) {
            $el->appendChild($doc->createElement('serialnumber', $this->serialNumber));
        }

        if ($this->purchase !== null) {
            $el->appendChild($this->purchase->toXml($doc));
        }

        if ($this->serviceInterval !== null) {
            $el->appendChild($doc->createElement('serviceinterval', (string) $this->serviceInterval));
        }

        if ($this->nextServiceDate !== null) {
            $nextServiceDate = $doc->createElement('nextservicedate');
            $nextServiceDate->appendChild($doc->createElement('datetime', $this->nextServiceDate->format('Y-m-d\TH:i:s')));
            $el->appendChild($nextServiceDate);
        }

        if ($this->notes !== null) {
            $el->appendChild($this->notes->toXml($doc));
        }

        return $el;
    }
}
