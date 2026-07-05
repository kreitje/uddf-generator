<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\TableGeneration;

use Kreitje\UddfGenerator\XmlSerializable;

final class TableGeneration implements XmlSerializable
{
    public function __construct(
        /** @var BaseCalculation[] */
        public readonly array $profiles = [],
        /** @var Table[] */
        public readonly array $tables = [],
        /** @var BottomTimeTable[] */
        public readonly array $bottomTimeTables = [],
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('tablegeneration');

        if ($this->profiles !== []) {
            $calculateProfile = $doc->createElement('calculateprofile');
            foreach ($this->profiles as $profile) {
                $calculateProfile->appendChild($profile->toXml($doc));
            }
            $el->appendChild($calculateProfile);
        }

        if ($this->tables !== []) {
            $calculateTable = $doc->createElement('calculatetable');
            foreach ($this->tables as $table) {
                $calculateTable->appendChild($table->toXml($doc));
            }
            $el->appendChild($calculateTable);
        }

        if ($this->bottomTimeTables !== []) {
            $calculateBottomTimeTable = $doc->createElement('calculatebottomtimetable');
            foreach ($this->bottomTimeTables as $bottomTimeTable) {
                $calculateBottomTimeTable->appendChild($bottomTimeTable->toXml($doc));
            }
            $el->appendChild($calculateBottomTimeTable);
        }

        return $el;
    }
}
