<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\DiveComputerControl;

use Kreitje\UddfGenerator\XmlSerializable;

final class SetDcData implements XmlSerializable
{
    public function __construct(
        /** @var DcAlarmWithTime[] */
        public readonly array $alarmTimes = [],
        public readonly ?float $altitude = null,
        public readonly ?string $buddyRef = null,
        public readonly ?\DateTimeImmutable $datetime = null,
        public readonly ?DcDecoModel $decoModel = null,
        /** @var DcAlarmWithDepth[] */
        public readonly array $diveDepthAlarms = [],
        /** @var DcDivePo2Alarm[] */
        public readonly array $divePo2Alarms = [],
        public readonly ?string $diveSiteRef = null,
        /** @var DcDiveTimeAlarm[] */
        public readonly array $diveTimeAlarms = [],
        public readonly ?DcAlarm $endNdtAlarm = null,
        public readonly ?DcGasDefinitionsData $gasDefinitionsData = null,
        public readonly bool $ownerData = false,
        public readonly ?string $password = null,
        public readonly bool $generatorData = false,
    ) {}

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $el = $doc->createElement('setdcdata');

        foreach ($this->alarmTimes as $alarmTime) {
            $el->appendChild($alarmTime->toXml($doc));
        }

        if ($this->altitude !== null) {
            $el->appendChild($doc->createElement('setdcaltitude', (string) $this->altitude));
        }

        if ($this->buddyRef !== null) {
            $el->appendChild($doc->createElement('setdcbuddydata'))->setAttribute('buddy', $this->buddyRef);
        }

        if ($this->datetime !== null) {
            $setDcDatetime = $doc->createElement('setdcdatetime');
            $setDcDatetime->appendChild($doc->createElement('datetime', $this->datetime->format('Y-m-d\TH:i:s')));
            $el->appendChild($setDcDatetime);
        }

        if ($this->decoModel !== null) {
            $el->appendChild($this->decoModel->toXml($doc));
        }

        foreach ($this->diveDepthAlarms as $alarm) {
            $el->appendChild($alarm->toXml($doc));
        }

        foreach ($this->divePo2Alarms as $alarm) {
            $el->appendChild($alarm->toXml($doc));
        }

        if ($this->diveSiteRef !== null) {
            $el->appendChild($doc->createElement('setdcdivesitedata'))->setAttribute('divesite', $this->diveSiteRef);
        }

        foreach ($this->diveTimeAlarms as $alarm) {
            $el->appendChild($alarm->toXml($doc));
        }

        if ($this->endNdtAlarm !== null) {
            $endNdtAlarm = $doc->createElement('setdcendndtalarm');
            $endNdtAlarm->appendChild($this->endNdtAlarm->toXml($doc));
            $el->appendChild($endNdtAlarm);
        }

        if ($this->gasDefinitionsData !== null) {
            $el->appendChild($this->gasDefinitionsData->toXml($doc));
        }

        if ($this->ownerData) {
            $el->appendChild($doc->createElement('setdcownerdata'));
        }

        if ($this->password !== null) {
            $el->appendChild($doc->createElement('setdcpassword', $this->password));
        }

        if ($this->generatorData) {
            $el->appendChild($doc->createElement('setdcgeneratordata'));
        }

        return $el;
    }
}
