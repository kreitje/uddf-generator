<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Parsing;

use Kreitje\UddfGenerator\DiveComputerControl\DcAlarm;
use Kreitje\UddfGenerator\DiveComputerControl\DcAlarmWithDepth;
use Kreitje\UddfGenerator\DiveComputerControl\DcAlarmWithTime;
use Kreitje\UddfGenerator\DiveComputerControl\DcDecoModel;
use Kreitje\UddfGenerator\DiveComputerControl\DcDivePo2Alarm;
use Kreitje\UddfGenerator\DiveComputerControl\DcDiveTimeAlarm;
use Kreitje\UddfGenerator\DiveComputerControl\DcGasDefinitionsData;
use Kreitje\UddfGenerator\DiveComputerControl\DiveComputerControl;
use Kreitje\UddfGenerator\DiveComputerControl\DiveComputerDump;
use Kreitje\UddfGenerator\DiveComputerControl\GetDcData;
use Kreitje\UddfGenerator\DiveComputerControl\SetDcData;

final class DiveComputerControlParser
{
    use DomHelpers;

    public function parse(\DOMElement $root): ?DiveComputerControl
    {
        $el = $this->child($root, 'divecomputercontrol');

        if ($el === null) {
            return null;
        }

        $setDcDataEl = $this->child($el, 'setdcdata');
        $getDcDataEl = $this->child($el, 'getdcdata');

        return new DiveComputerControl(
            setDcData: $setDcDataEl !== null ? $this->parseSetDcData($setDcDataEl) : null,
            getDcData: $getDcDataEl !== null ? new GetDcData(
                allData: $this->marker($getDcDataEl, 'getdcalldata'),
                generatorData: $this->marker($getDcDataEl, 'getdcgeneratordata'),
                ownerData: $this->marker($getDcDataEl, 'getdcownerdata'),
                buddyData: $this->marker($getDcDataEl, 'getdcbuddydata'),
                gasDefinitionsData: $this->marker($getDcDataEl, 'getdcgasdefinitionsdata'),
                diveSiteData: $this->marker($getDcDataEl, 'getdcdivesitedata'),
                diveTripData: $this->marker($getDcDataEl, 'getdcdivetripdata'),
                profileData: $this->marker($getDcDataEl, 'getdcprofiledata'),
            ) : null,
            diveComputerDumps: array_map(
                fn (\DOMElement $e): DiveComputerDump => $this->parseDiveComputerDump($e),
                $this->children($el, 'divecomputerdump'),
            ),
        );
    }

    private function parseSetDcData(\DOMElement $el): SetDcData
    {
        $buddyDataEl = $this->child($el, 'setdcbuddydata');
        $decoModelEl = $this->child($el, 'setdcdecomodel');
        $diveSiteDataEl = $this->child($el, 'setdcdivesitedata');
        $endNdtAlarmEl = $this->child($el, 'setdcendndtalarm');
        $gasDefinitionsDataEl = $this->child($el, 'setdcgasdefinitionsdata');
        $datetimeStr = null;
        $datetimeWrapperEl = $this->child($el, 'setdcdatetime');
        if ($datetimeWrapperEl !== null) {
            $datetimeStr = $this->text($datetimeWrapperEl, 'datetime');
        }

        return new SetDcData(
            alarmTimes: array_map(
                fn (\DOMElement $e): DcAlarmWithTime => new DcAlarmWithTime(
                    datetime: new \DateTimeImmutable($this->require($e, 'datetime')),
                    dcAlarm: $this->parseDcAlarm($this->child($e, 'dcalarm')),
                ),
                $this->children($el, 'setdcalarmtime'),
            ),
            altitude: $this->float($el, 'setdcaltitude'),
            buddyRef: $buddyDataEl?->getAttribute('buddy'),
            datetime: $datetimeStr !== null ? new \DateTimeImmutable($datetimeStr) : null,
            decoModel: $decoModelEl !== null ? new DcDecoModel(
                name: $this->require($decoModelEl, 'name'),
                aliasNames: $this->texts($decoModelEl, 'aliasname'),
                applicationData: $this->parseApplicationData($decoModelEl),
            ) : null,
            diveDepthAlarms: array_map(
                fn (\DOMElement $e): DcAlarmWithDepth => new DcAlarmWithDepth(
                    dcAlarmDepth: $this->float($e, 'dcalarmdepth') ?? 0.0,
                    dcAlarm: $this->parseDcAlarm($this->child($e, 'dcalarm')),
                ),
                $this->children($el, 'setdcdivedepthalarm'),
            ),
            divePo2Alarms: array_map(
                fn (\DOMElement $e): DcDivePo2Alarm => new DcDivePo2Alarm(
                    maximumPo2: $this->float($e, 'maximumpo2') ?? 0.0,
                    dcAlarm: $this->parseDcAlarm($this->child($e, 'dcalarm')),
                ),
                $this->children($el, 'setdcdivepo2alarm'),
            ),
            diveSiteRef: $diveSiteDataEl?->getAttribute('divesite'),
            diveTimeAlarms: array_map(
                fn (\DOMElement $e): DcDiveTimeAlarm => new DcDiveTimeAlarm(
                    timeSpan: $this->float($e, 'timespan') ?? 0.0,
                    dcAlarm: $this->parseDcAlarm($this->child($e, 'dcalarm')),
                ),
                $this->children($el, 'setdcdivetimealarm'),
            ),
            endNdtAlarm: $endNdtAlarmEl !== null ? $this->parseDcAlarm($this->child($endNdtAlarmEl, 'dcalarm')) : null,
            gasDefinitionsData: $gasDefinitionsDataEl !== null ? new DcGasDefinitionsData(
                allGasDefinitions: $this->marker($gasDefinitionsDataEl, 'setdcallgasdefinitions'),
                gasDataRefs: $this->texts($gasDefinitionsDataEl, 'setdcgasdata'),
            ) : null,
            ownerData: $this->marker($el, 'setdcownerdata'),
            password: $this->text($el, 'setdcpassword'),
            generatorData: $this->marker($el, 'setdcgeneratordata'),
        );
    }

    private function parseDcAlarm(?\DOMElement $el): DcAlarm
    {
        if ($el === null) {
            return new DcAlarm(alarmType: 0);
        }

        return new DcAlarm(
            alarmType: $this->int($el, 'alarmType') ?? 0,
            acknowledge: $this->marker($el, 'acknowledge'),
            period: $this->float($el, 'period'),
        );
    }

    private function parseDiveComputerDump(\DOMElement $el): DiveComputerDump
    {
        $linkEl = $this->child($el, 'link');

        return new DiveComputerDump(
            linkRef: $linkEl?->getAttribute('ref') ?? '',
            datetime: new \DateTimeImmutable($this->require($el, 'datetime')),
            dcDumpBase64: $this->require($el, 'dcdump'),
        );
    }
}
