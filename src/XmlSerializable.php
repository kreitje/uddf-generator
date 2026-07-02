<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator;

interface XmlSerializable
{
    public function toXml(\DOMDocument $doc): \DOMElement;
}
