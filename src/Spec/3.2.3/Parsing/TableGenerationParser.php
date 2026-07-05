<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Parsing;

use Kreitje\UddfGenerator\Spec\V323\ProfileData\InputProfile;
use Kreitje\UddfGenerator\Spec\V323\ProfileData\Waypoint;
use Kreitje\UddfGenerator\Spec\V323\TableGeneration\BaseCalculation;
use Kreitje\UddfGenerator\Spec\V323\TableGeneration\BottomTimeTable;
use Kreitje\UddfGenerator\Spec\V323\TableGeneration\BottomTimeTableScope;
use Kreitje\UddfGenerator\Spec\V323\TableGeneration\MixChange;
use Kreitje\UddfGenerator\Spec\V323\TableGeneration\Output;
use Kreitje\UddfGenerator\Spec\V323\TableGeneration\Table;
use Kreitje\UddfGenerator\Spec\V323\TableGeneration\TableGeneration;
use Kreitje\UddfGenerator\Spec\V323\TableGeneration\TableScope;

final class TableGenerationParser
{
    use DomHelpers;

    public function parse(\DOMElement $root): ?TableGeneration
    {
        $el = $this->child($root, 'tablegeneration');

        if ($el === null) {
            return null;
        }

        $calculateProfileEl = $this->child($el, 'calculateprofile');
        $calculateTableEl = $this->child($el, 'calculatetable');
        $calculateBottomTimeTableEl = $this->child($el, 'calculatebottomtimetable');

        return new TableGeneration(
            profiles: $calculateProfileEl !== null
                ? array_map(fn (\DOMElement $e): BaseCalculation => $this->parseBaseCalculation($e), $this->children($calculateProfileEl, 'profile'))
                : [],
            tables: $calculateTableEl !== null
                ? array_map(fn (\DOMElement $e): Table => $this->parseTable($e), $this->children($calculateTableEl, 'table'))
                : [],
            bottomTimeTables: $calculateBottomTimeTableEl !== null
                ? array_map(fn (\DOMElement $e): BottomTimeTable => $this->parseBottomTimeTable($e), $this->children($calculateBottomTimeTableEl, 'bottomtimetable'))
                : [],
        );
    }

    private function parseBaseCalculation(\DOMElement $el): BaseCalculation
    {
        return new BaseCalculation(...$this->parseBaseCalculationFields($el));
    }

    private function parseTable(\DOMElement $el): Table
    {
        $tableScopeEl = $this->child($el, 'tablescope');

        return new Table(
            ...$this->parseBaseCalculationFields($el),
            tableScope: $tableScopeEl !== null ? new TableScope(
                altitude: $this->float($tableScopeEl, 'altitude'),
                diveDepthBegin: $this->float($tableScopeEl, 'divedepthbegin'),
                diveDepthEnd: $this->float($tableScopeEl, 'divedepthend'),
                diveDepthStep: $this->float($tableScopeEl, 'divedepthstep'),
                bottomTimeMaximum: $this->float($tableScopeEl, 'bottomtimemaximum'),
                bottomTimeMinimum: $this->float($tableScopeEl, 'bottomtimeminimum'),
                bottomTimeStepBegin: $this->float($tableScopeEl, 'bottomtimestepbegin'),
                bottomTimeStepEnd: $this->float($tableScopeEl, 'bottomtimestepend'),
            ) : new TableScope(),
        );
    }

    /** @return array<string, mixed> */
    private function parseBaseCalculationFields(\DOMElement $el): array
    {
        $outputEl = $this->child($el, 'output');
        $mixChangeEl = $this->child($el, 'mixchange');
        $inputProfileEl = $this->child($el, 'inputprofile');
        $surfaceIntervalAfterEl = $this->child($el, 'surfaceintervalafterdive');
        $surfaceIntervalBeforeEl = $this->child($el, 'surfaceintervalbeforedive');

        return [
            'id' => $el->getAttribute('id'),
            'title' => $this->text($el, 'title'),
            'linkRefs' => array_map(static fn (\DOMElement $l): string => $l->getAttribute('ref'), $this->children($el, 'link')),
            'surfaceIntervalAfterDive' => $surfaceIntervalAfterEl !== null ? $this->parseSurfaceInterval($surfaceIntervalAfterEl) : null,
            'surfaceIntervalBeforeDive' => $surfaceIntervalBeforeEl !== null ? $this->parseSurfaceInterval($surfaceIntervalBeforeEl) : null,
            'density' => $this->float($el, 'density'),
            'maximumAscendingRate' => $this->float($el, 'maximumascendingrate'),
            'output' => $outputEl !== null ? new Output(
                lingo: $this->text($outputEl, 'lingo'),
                fileFormat: $this->text($outputEl, 'fileformat'),
                filename: $this->text($outputEl, 'filename'),
                headline: $this->text($outputEl, 'headline'),
                remark: $this->text($outputEl, 'remark'),
            ) : null,
            'applicationData' => $this->parseApplicationData($el),
            'decoModel' => $this->text($el, 'decomodel'),
            'deepStopTime' => $this->float($el, 'deepstoptime'),
            'mixChange' => $mixChangeEl !== null ? new MixChange(
                ascent: $this->parseMixChangeWaypoints($mixChangeEl, 'ascent'),
                descent: $this->parseMixChangeWaypoints($mixChangeEl, 'descent'),
            ) : null,
            'inputProfile' => $inputProfileEl !== null ? new InputProfile(
                linkRefs: array_map(static fn (\DOMElement $l): string => $l->getAttribute('ref'), $this->children($inputProfileEl, 'link')),
                waypoints: array_map(fn (\DOMElement $wp): Waypoint => $this->parseWaypoint($wp), $this->children($inputProfileEl, 'waypoint')),
            ) : null,
        ];
    }

    /** @return Waypoint[] */
    private function parseMixChangeWaypoints(\DOMElement $mixChangeEl, string $elementName): array
    {
        $wrapperEl = $this->child($mixChangeEl, $elementName);

        if ($wrapperEl === null) {
            return [];
        }

        return array_map(
            fn (\DOMElement $wp): Waypoint => $this->parseWaypoint($wp),
            $this->children($wrapperEl, 'waypoint'),
        );
    }

    private function parseBottomTimeTable(\DOMElement $el): BottomTimeTable
    {
        $outputEl = $this->child($el, 'output');
        $scopeEl = $this->child($el, 'bottomtimetablescope');

        return new BottomTimeTable(
            id: $el->getAttribute('id'),
            bottomTimeTableScope: $scopeEl !== null ? new BottomTimeTableScope(
                diveDepthBegin: $this->float($scopeEl, 'divedepthbegin') ?? 0.0,
                diveDepthEnd: $this->float($scopeEl, 'divedepthend') ?? 0.0,
                diveDepthStep: $this->float($scopeEl, 'divedepthstep') ?? 0.0,
                breathingConsumptionVolumeBegin: $this->float($scopeEl, 'breathingconsumptionvolumebegin') ?? 0.0,
                breathingConsumptionVolumeEnd: $this->float($scopeEl, 'breathingconsumptionvolumeend') ?? 0.0,
                breathingConsumptionVolumeStep: $this->float($scopeEl, 'breathingconsumptionvolumestep') ?? 0.0,
                tankVolumeBegin: $this->float($scopeEl, 'tankvolumebegin') ?? 0.0,
                tankVolumeEnd: $this->float($scopeEl, 'tankvolumeend') ?? 0.0,
                tankVolumeStep: $this->float($scopeEl, 'tankvolumestep') ?? 0.0,
                tankPressureBegin: $this->float($scopeEl, 'tankpressurebegin') ?? 0.0,
                tankPressureReserve: $this->float($scopeEl, 'tankpressurereserve') ?? 0.0,
            ) : new BottomTimeTableScope(0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0),
            title: $this->text($el, 'title'),
            linkRefs: array_map(static fn (\DOMElement $l): string => $l->getAttribute('ref'), $this->children($el, 'link')),
            output: $outputEl !== null ? new Output(
                lingo: $this->text($outputEl, 'lingo'),
                fileFormat: $this->text($outputEl, 'fileformat'),
                filename: $this->text($outputEl, 'filename'),
                headline: $this->text($outputEl, 'headline'),
                remark: $this->text($outputEl, 'remark'),
            ) : null,
            applicationData: $this->parseApplicationData($el),
        );
    }
}
