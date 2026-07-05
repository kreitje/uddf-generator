<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Tests;

use Kreitje\UddfGenerator\Business\Business;
use Kreitje\UddfGenerator\Common\Address;
use Kreitje\UddfGenerator\Common\Contact;
use Kreitje\UddfGenerator\Common\Notes;
use Kreitje\UddfGenerator\Common\Price;
use Kreitje\UddfGenerator\Common\Rating;
use Kreitje\UddfGenerator\Common\Shop;
use Kreitje\UddfGenerator\DecoModel\Buehlmann;
use Kreitje\UddfGenerator\DecoModel\DecoModel;
use Kreitje\UddfGenerator\DecoModel\Rgbm;
use Kreitje\UddfGenerator\DecoModel\Tissue;
use Kreitje\UddfGenerator\DecoModel\Vpm;
use Kreitje\UddfGenerator\DiveComputerControl\DcAlarm;
use Kreitje\UddfGenerator\DiveComputerControl\DcAlarmWithDepth;
use Kreitje\UddfGenerator\DiveComputerControl\DcAlarmWithTime;
use Kreitje\UddfGenerator\DiveComputerControl\DcDivePo2Alarm;
use Kreitje\UddfGenerator\DiveComputerControl\DcDiveTimeAlarm;
use Kreitje\UddfGenerator\DiveComputerControl\DiveComputerControl;
use Kreitje\UddfGenerator\DiveComputerControl\DiveComputerDump;
use Kreitje\UddfGenerator\DiveComputerControl\GetDcData;
use Kreitje\UddfGenerator\DiveComputerControl\SetDcData;
use Kreitje\UddfGenerator\Diver\Buddy;
use Kreitje\UddfGenerator\Diver\Camera;
use Kreitje\UddfGenerator\Diver\Certification;
use Kreitje\UddfGenerator\Diver\Diver;
use Kreitje\UddfGenerator\Diver\Doctor;
use Kreitje\UddfGenerator\Diver\Equipment;
use Kreitje\UddfGenerator\Diver\EquipmentPiece;
use Kreitje\UddfGenerator\Diver\Examination;
use Kreitje\UddfGenerator\Diver\Insurance;
use Kreitje\UddfGenerator\Diver\Instructor;
use Kreitje\UddfGenerator\Diver\Owner;
use Kreitje\UddfGenerator\Diver\PersonalData;
use Kreitje\UddfGenerator\Diver\Permit;
use Kreitje\UddfGenerator\Diver\Suit;
use Kreitje\UddfGenerator\Diver\Tank;
use Kreitje\UddfGenerator\DiveSite\Divebase;
use Kreitje\UddfGenerator\DiveSite\DiveSite;
use Kreitje\UddfGenerator\DiveSite\Ecology;
use Kreitje\UddfGenerator\DiveSite\Fauna;
use Kreitje\UddfGenerator\DiveSite\Geography;
use Kreitje\UddfGenerator\DiveSite\Invertebrata;
use Kreitje\UddfGenerator\DiveSite\SingleLifeForm;
use Kreitje\UddfGenerator\DiveSite\SiteData;
use Kreitje\UddfGenerator\DiveSite\Species;
use Kreitje\UddfGenerator\DiveTrip\DiveTrip;
use Kreitje\UddfGenerator\DiveTrip\Operator;
use Kreitje\UddfGenerator\DiveTrip\Trip;
use Kreitje\UddfGenerator\DiveTrip\TripPart;
use Kreitje\UddfGenerator\DiveTrip\Vessel;
use Kreitje\UddfGenerator\Enum\AlarmType;
use Kreitje\UddfGenerator\Enum\DecoStopKind;
use Kreitje\UddfGenerator\Enum\Environment;
use Kreitje\UddfGenerator\Enum\ExaminationResult;
use Kreitje\UddfGenerator\Enum\GeneratorType;
use Kreitje\UddfGenerator\Enum\GlobalAlarm;
use Kreitje\UddfGenerator\Enum\Sex;
use Kreitje\UddfGenerator\Enum\SuitType;
use Kreitje\UddfGenerator\Enum\TankMaterial;
use Kreitje\UddfGenerator\Enum\TissueGas;
use Kreitje\UddfGenerator\Gas\GasDefinitions;
use Kreitje\UddfGenerator\Gas\Mix;
use Kreitje\UddfGenerator\Generator\Generator;
use Kreitje\UddfGenerator\Generator\Manufacturer;
use Kreitje\UddfGenerator\Maker\Maker;
use Kreitje\UddfGenerator\Media\Media;
use Kreitje\UddfGenerator\Media\MediaData;
use Kreitje\UddfGenerator\ProfileData\Alarm;
use Kreitje\UddfGenerator\ProfileData\Dive;
use Kreitje\UddfGenerator\ProfileData\InformationAfterDive;
use Kreitje\UddfGenerator\ProfileData\InformationBeforeDive;
use Kreitje\UddfGenerator\ProfileData\ProfileData;
use Kreitje\UddfGenerator\ProfileData\RepetitionGroup;
use Kreitje\UddfGenerator\ProfileData\TankData;
use Kreitje\UddfGenerator\ProfileData\TankPressureReading;
use Kreitje\UddfGenerator\ProfileData\Waypoint;
use Kreitje\UddfGenerator\TableGeneration\BaseCalculation;
use Kreitje\UddfGenerator\TableGeneration\Table;
use Kreitje\UddfGenerator\TableGeneration\TableGeneration;
use Kreitje\UddfGenerator\TableGeneration\TableScope;
use Kreitje\UddfGenerator\UddfGenerator;
use Kreitje\UddfGenerator\UddfParser;
use PHPUnit\Framework\TestCase;

/**
 * Exercises every top-level UDDF 3.2.3 section this library models (see
 * uddf_3.2.3.xsd) in a single generate() -> parse() round trip, to catch
 * cross-section interactions the per-phase tests elsewhere miss.
 */
final class UddfFullSpecTest extends TestCase
{
    private function buildFullUddf(): UddfGenerator
    {
        return new UddfGenerator(
            generator: new Generator(
                name: 'FullSpec App',
                type: GeneratorType::Logbook,
                linkRef: 'mfr_1',
                manufacturer: new Manufacturer(id: 'mfr_1', name: 'Acme Diving'),
                version: '9.9.9',
            ),
            media: new MediaData(audio: [new Media(id: 'a1', objectName: 'clip.mp3')]),
            maker: new Maker(manufacturers: [new Manufacturer(id: 'mfr_2', name: 'Widget Co')]),
            business: new Business(shops: [new Shop(id: 'shop_1', name: 'Dive Shop')]),
            diver: new Diver(
                owner: new Owner(
                    id: 'owner_1',
                    personalData: new PersonalData(firstName: 'Jane', lastName: 'Doe', sex: Sex::Female),
                    address: new Address(country: 'AU'),
                    contact: new Contact(emails: ['jane@example.com']),
                    equipment: new Equipment(
                        tanks: [new Tank(id: 'tank_a', name: 'Alu80', volume: 11.1, tankMaterial: TankMaterial::Aluminium)],
                        suits: [new Suit(id: 'suit_1', name: 'Wetsuit', suitType: SuitType::WetSuit)],
                        cameras: [new Camera(id: 'cam_1', bodies: [new EquipmentPiece(id: 'body_1', name: 'Body')])],
                    ),
                    medicalExaminations: [new Examination(
                        id: 'exam_1',
                        doctor: new Doctor(id: 'doc_1', personalData: new PersonalData(firstName: 'Doc', lastName: 'Tor')),
                        result: ExaminationResult::Passed,
                    )],
                    certifications: [new Certification(
                        level: 'AOW',
                        instructor: new Instructor(id: 'inst_1', personalData: new PersonalData(firstName: 'In', lastName: 'Structor')),
                    )],
                    divePermits: [new Permit(name: 'Cave Permit')],
                    diveInsurances: [new Insurance(name: 'DAN')],
                ),
                buddies: [new Buddy(id: 'buddy_1', personalData: new PersonalData(firstName: 'Bud', lastName: 'Dy'), isStudent: true)],
            ),
            diveSites: [
                new DiveSite(
                    id: 'site_gbr',
                    name: 'Great Barrier Reef',
                    environment: Environment::OceanSea,
                    geography: new Geography(location: 'Queensland', latitude: -18.2871, longitude: 147.6992),
                    ecology: new Ecology(fauna: new Fauna(invertebrata: new Invertebrata(
                        mollusca: new SingleLifeForm(species: [new Species(id: 'sp1', trivialName: 'Octopus')]),
                    ))),
                    siteData: new SiteData(maximumDepth: 30.0),
                    ratings: [new Rating(value: 9)],
                    notes: new Notes(paragraphs: ['UNESCO World Heritage Site']),
                ),
            ],
            diveBases: [new Divebase(id: 'base_1', name: 'GBR Dive Base', pricePerDive: new Price(amount: 50.0, currency: 'AUD'))],
            diveTrip: new DiveTrip(trips: [
                new Trip(id: 'trip_1', name: 'Reef Trip', tripParts: [
                    new TripPart(
                        name: 'Boat leg',
                        operator: new Operator(name: 'Best Diving Co'),
                        vessel: new Vessel(id: 'vessel_1', name: 'MV Blue Fin'),
                    ),
                ]),
            ]),
            gasDefinitions: new GasDefinitions([
                Mix::air(),
                Mix::nitrox('ean32', 0.32),
            ]),
            decoModel: new DecoModel(
                buehlmann: new Buehlmann(id: 'bm_1', tissues: [new Tissue(gas: TissueGas::N2, number: 1, halfLife: 5.0, a: 1.1, b: 0.9)]),
                rgbm: new Rgbm(id: 'rgbm_1', tissues: [new Tissue(gas: TissueGas::He, number: 1, halfLife: 8.0, a: 1.0, b: 0.8)]),
                vpm: new Vpm(id: 'vpm_1', tissues: [new Tissue(gas: TissueGas::N2, number: 2, halfLife: 10.0, a: 0.9, b: 0.7)]),
            ),
            profileData: new ProfileData([
                new RepetitionGroup(id: 'rg_1', dives: [
                    new Dive(
                        id: 'dive_99',
                        informationBeforeDive: new InformationBeforeDive(
                            datetime: new \DateTimeImmutable('2024-06-01T09:00:00'),
                            diveNumber: 99,
                            diveSiteRef: 'site_gbr',
                        ),
                        samples: [
                            new Waypoint(depth: 0.0, diveTime: 0, switchMixRef: 'air'),
                            new Waypoint(
                                depth: 25.0,
                                diveTime: 180,
                                temperature: 300.15,
                                tankPressures: [new TankPressureReading(value: 190.0, ref: 'tank_a')],
                                alarms: [new Alarm(type: AlarmType::Deco)],
                                decoStops: [new \Kreitje\UddfGenerator\ProfileData\DecoStop(kind: DecoStopKind::Safety, decoDepth: 5.0, duration: 180.0)],
                            ),
                            new Waypoint(depth: 0.0, diveTime: 1800),
                        ],
                        informationAfterDive: new InformationAfterDive(
                            greatestDepth: 25.0,
                            diveDuration: 1800.0,
                            rating: new Rating(value: 8),
                            globalAlarmsGiven: [GlobalAlarm::SosMode],
                        ),
                        tankData: [new TankData(tankRef: 'tank_a', mixRef: 'air', tankPressureBegin: 200.0, tankPressureEnd: 50.0, breathingConsumptionVolume: 18.0)],
                    ),
                ]),
            ]),
            tableGeneration: new TableGeneration(
                profiles: [new BaseCalculation(id: 'p1', title: 'Profile 1')],
                tables: [new Table(id: 't1', tableScope: new TableScope(diveDepthBegin: 10.0, diveDepthEnd: 40.0))],
            ),
            diveComputerControl: new DiveComputerControl(
                setDcData: new SetDcData(
                    alarmTimes: [new DcAlarmWithTime(datetime: new \DateTimeImmutable('2024-01-01T08:00:00'), dcAlarm: new DcAlarm(alarmType: 1))],
                    diveDepthAlarms: [new DcAlarmWithDepth(dcAlarmDepth: 30.0, dcAlarm: new DcAlarm(alarmType: 2))],
                    divePo2Alarms: [new DcDivePo2Alarm(maximumPo2: 1.4, dcAlarm: new DcAlarm(alarmType: 3))],
                    diveTimeAlarms: [new DcDiveTimeAlarm(timeSpan: 3600.0, dcAlarm: new DcAlarm(alarmType: 4))],
                ),
                getDcData: new GetDcData(allData: true),
                diveComputerDumps: [new DiveComputerDump(linkRef: 'dc1', datetime: new \DateTimeImmutable('2024-01-02T00:00:00'), dcDumpBase64: base64_encode('data'))],
            ),
        );
    }

    public function testGeneratesValidXml(): void
    {
        $xml = $this->buildFullUddf()->generate();

        $doc = new \DOMDocument();
        $this->assertTrue($doc->loadXML($xml));
    }

    public function testRoundtripGeneratorAndMediaMakerBusiness(): void
    {
        $parsed = (new UddfParser())->parse($this->buildFullUddf()->generate());

        $this->assertSame(GeneratorType::Logbook, $parsed->generator->type);
        $this->assertSame('mfr_1', $parsed->generator->linkRef);
        $this->assertSame('clip.mp3', $parsed->media->audio[0]->objectName);
        $this->assertSame('Widget Co', $parsed->maker->manufacturers[0]->name);
        $this->assertSame('Dive Shop', $parsed->business->shops[0]->name);
    }

    public function testRoundtripDiver(): void
    {
        $owner = (new UddfParser())->parse($this->buildFullUddf()->generate())->diver->owner;

        $this->assertSame('AU', $owner->address?->country);
        $this->assertSame(['jane@example.com'], $owner->contact?->emails);
        $this->assertSame(TankMaterial::Aluminium, $owner->equipment->tanks[0]->tankMaterial);
        $this->assertSame(SuitType::WetSuit, $owner->equipment->suits[0]->suitType);
        $this->assertSame('Body', $owner->equipment->cameras[0]->bodies[0]->name);
        $this->assertSame(ExaminationResult::Passed, $owner->medicalExaminations[0]->result);
        $this->assertSame('Doc', $owner->medicalExaminations[0]->doctor->personalData->firstName);
        $this->assertSame('In', $owner->certifications[0]->instructor->personalData->firstName);
        $this->assertSame('Cave Permit', $owner->divePermits[0]->name);
        $this->assertSame('DAN', $owner->diveInsurances[0]->name);
    }

    public function testRoundtripBuddy(): void
    {
        $buddies = (new UddfParser())->parse($this->buildFullUddf()->generate())->diver->buddies;

        $this->assertCount(1, $buddies);
        $this->assertTrue($buddies[0]->isStudent);
        $this->assertSame('Bud', $buddies[0]->personalData->firstName);
    }

    public function testRoundtripDiveSiteAndDivebase(): void
    {
        $parsed = (new UddfParser())->parse($this->buildFullUddf()->generate());

        $site = $parsed->diveSites[0];
        $this->assertSame(Environment::OceanSea, $site->environment);
        $this->assertSame('Octopus', $site->ecology->fauna->invertebrata->mollusca->species[0]->trivialName);
        $this->assertSame(30.0, $site->siteData->maximumDepth);
        $this->assertSame(9, $site->ratings[0]->value);

        $this->assertSame('GBR Dive Base', $parsed->diveBases[0]->name);
        $this->assertSame(50.0, $parsed->diveBases[0]->pricePerDive->amount);
    }

    public function testRoundtripDiveTrip(): void
    {
        $trip = (new UddfParser())->parse($this->buildFullUddf()->generate())->diveTrip->trips[0];

        $this->assertSame('Reef Trip', $trip->name);
        $this->assertSame('Best Diving Co', $trip->tripParts[0]->operator->name);
        $this->assertSame('MV Blue Fin', $trip->tripParts[0]->vessel->name);
    }

    public function testRoundtripGasDefinitionsAndDecoModel(): void
    {
        $parsed = (new UddfParser())->parse($this->buildFullUddf()->generate());

        $this->assertCount(2, $parsed->gasDefinitions->mixes);
        $this->assertSame(TissueGas::N2, $parsed->decoModel->buehlmann->tissues[0]->gas);
        $this->assertSame(TissueGas::He, $parsed->decoModel->rgbm->tissues[0]->gas);
        $this->assertCount(1, $parsed->decoModel->vpm->tissues);
    }

    public function testRoundtripProfileData(): void
    {
        $dive = (new UddfParser())->parse($this->buildFullUddf()->generate())->profileData->repetitionGroups[0]->dives[0];

        $this->assertSame('site_gbr', $dive->informationBeforeDive->diveSiteRef);
        $this->assertSame(190.0, $dive->samples[1]->tankPressures[0]->value);
        $this->assertSame('tank_a', $dive->samples[1]->tankPressures[0]->ref);
        $this->assertSame(AlarmType::Deco, $dive->samples[1]->alarms[0]->type);
        $this->assertSame(DecoStopKind::Safety, $dive->samples[1]->decoStops[0]->kind);
        $this->assertSame(8, $dive->informationAfterDive->rating->value);
        $this->assertSame([GlobalAlarm::SosMode], $dive->informationAfterDive->globalAlarmsGiven);
        $this->assertSame(18.0, $dive->tankData[0]->breathingConsumptionVolume);
    }

    public function testRoundtripTableGeneration(): void
    {
        $tg = (new UddfParser())->parse($this->buildFullUddf()->generate())->tableGeneration;

        $this->assertSame('Profile 1', $tg->profiles[0]->title);
        $this->assertSame(40.0, $tg->tables[0]->tableScope->diveDepthEnd);
    }

    public function testRoundtripDiveComputerControl(): void
    {
        $dcc = (new UddfParser())->parse($this->buildFullUddf()->generate())->diveComputerControl;

        $this->assertSame(1, $dcc->setDcData->alarmTimes[0]->dcAlarm->alarmType);
        $this->assertSame(30.0, $dcc->setDcData->diveDepthAlarms[0]->dcAlarmDepth);
        $this->assertSame(1.4, $dcc->setDcData->divePo2Alarms[0]->maximumPo2);
        $this->assertSame(3600.0, $dcc->setDcData->diveTimeAlarms[0]->timeSpan);
        $this->assertTrue($dcc->getDcData->allData);
        $this->assertSame('data', base64_decode($dcc->diveComputerDumps[0]->dcDumpBase64));
    }
}
