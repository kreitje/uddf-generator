<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator\Spec\V323\Parsing;

use Kreitje\UddfGenerator\Spec\V323\Common\Shop;
use Kreitje\UddfGenerator\Spec\V323\Diver\Buddy;
use Kreitje\UddfGenerator\Spec\V323\Diver\Camera;
use Kreitje\UddfGenerator\Spec\V323\Diver\Certification;
use Kreitje\UddfGenerator\Spec\V323\Diver\Diver;
use Kreitje\UddfGenerator\Spec\V323\Diver\Doctor;
use Kreitje\UddfGenerator\Spec\V323\Diver\Equipment;
use Kreitje\UddfGenerator\Spec\V323\Diver\EquipmentConfiguration;
use Kreitje\UddfGenerator\Spec\V323\Diver\EquipmentPiece;
use Kreitje\UddfGenerator\Spec\V323\Diver\Examination;
use Kreitje\UddfGenerator\Spec\V323\Diver\Insurance;
use Kreitje\UddfGenerator\Spec\V323\Diver\Instructor;
use Kreitje\UddfGenerator\Spec\V323\Diver\Membership;
use Kreitje\UddfGenerator\Spec\V323\Diver\NumberOfDives;
use Kreitje\UddfGenerator\Spec\V323\Diver\Owner;
use Kreitje\UddfGenerator\Spec\V323\Diver\PersonalData;
use Kreitje\UddfGenerator\Spec\V323\Diver\Permit;
use Kreitje\UddfGenerator\Spec\V323\Diver\Purchase;
use Kreitje\UddfGenerator\Spec\V323\Diver\Rebreather;
use Kreitje\UddfGenerator\Spec\V323\Diver\Suit;
use Kreitje\UddfGenerator\Spec\V323\Diver\Tank;
use Kreitje\UddfGenerator\Spec\V323\Diver\Videocamera;
use Kreitje\UddfGenerator\Spec\V323\Enum\ExaminationResult;
use Kreitje\UddfGenerator\Spec\V323\Enum\Sex;
use Kreitje\UddfGenerator\Spec\V323\Enum\Smoking;
use Kreitje\UddfGenerator\Spec\V323\Enum\SuitType;
use Kreitje\UddfGenerator\Spec\V323\Enum\TankMaterial;
use Kreitje\UddfGenerator\ParseException;

final class DiverParser
{
    use DomHelpers;

    public function parse(\DOMElement $root): ?Diver
    {
        $diverEl = $this->child($root, 'diver');

        if ($diverEl === null) {
            return null;
        }

        $ownerEl = $this->child($diverEl, 'owner');

        if ($ownerEl === null) {
            return null;
        }

        return new Diver(
            owner: new Owner(...$this->parsePersonFields($ownerEl), id: $ownerEl->getAttribute('id') ?: 'owner'),
            buddies: array_map(
                fn (\DOMElement $buddyEl): Buddy => new Buddy(
                    ...$this->parsePersonFields($buddyEl),
                    id: $buddyEl->getAttribute('id'),
                    isStudent: $this->marker($buddyEl, 'student'),
                ),
                $this->children($diverEl, 'buddy'),
            ),
        );
    }

    /** @return array<string, mixed> */
    private function parsePersonFields(\DOMElement $el): array
    {
        $personalEl = $this->child($el, 'personal');

        if ($personalEl === null) {
            throw new ParseException('Missing required <personal> element.');
        }

        return [
            'personalData' => $this->parsePersonalData($personalEl),
            'address' => $this->parseAddress($el),
            'contact' => $this->parseContact($el),
            'equipment' => $this->parseEquipment($el),
            'medicalExaminations' => $this->parseExaminations($el),
            'certifications' => $this->parseCertifications($el),
            'divePermits' => $this->parsePermits($el),
            'diveInsurances' => $this->parseInsurances($el),
            'notes' => $this->parseNotes($el),
        ];
    }

    private function parsePersonalData(\DOMElement $personalEl): PersonalData
    {
        $membershipEl = $this->child($personalEl, 'membership');
        $numberOfDivesEl = $this->child($personalEl, 'numberofdives');

        return new PersonalData(
            firstName: $this->text($personalEl, 'firstname') ?? '',
            lastName: $this->text($personalEl, 'lastname') ?? '',
            middleName: $this->text($personalEl, 'middlename'),
            birthName: $this->text($personalEl, 'birthname'),
            honorific: $this->text($personalEl, 'honorific'),
            sex: $this->parseEnum(Sex::class, $personalEl, 'sex'),
            birthdate: $this->parseEncapsulatedDateTime($personalEl, 'birthdate'),
            passport: $this->text($personalEl, 'passport'),
            membership: $membershipEl !== null ? new Membership(
                organisation: $membershipEl->getAttribute('organisation'),
                memberId: $this->attr($membershipEl, 'memberid'),
            ) : null,
            height: $this->float($personalEl, 'height'),
            weight: $this->float($personalEl, 'weight'),
            bloodGroup: $this->text($personalEl, 'bloodgroup'),
            smoking: $this->parseEnum(Smoking::class, $personalEl, 'smoking'),
            numberOfDives: $numberOfDivesEl !== null ? new NumberOfDives(
                startDate: new \DateTimeImmutable($numberOfDivesEl->getAttribute('startdate')),
                endDate: new \DateTimeImmutable($numberOfDivesEl->getAttribute('enddate')),
                dives: (int) $numberOfDivesEl->getAttribute('dives'),
            ) : null,
        );
    }

    private function parseEquipment(\DOMElement $personEl): ?Equipment
    {
        $el = $this->child($personEl, 'equipment');

        if ($el === null) {
            return null;
        }

        return new Equipment(
            boots: $this->parsePieces($el, 'boots'),
            buoyancyControlDevices: $this->parsePieces($el, 'buoyancycontroldevice'),
            cameras: array_map(fn (\DOMElement $e): Camera => $this->parseCamera($e), $this->children($el, 'camera')),
            compasses: $this->parsePieces($el, 'compass'),
            compressors: $this->parsePieces($el, 'compressor'),
            diveComputers: $this->parsePieces($el, 'divecomputer'),
            equipmentConfigurations: array_map(
                fn (\DOMElement $e): EquipmentConfiguration => $this->parseEquipmentConfiguration($e),
                $this->children($el, 'equipmentconfiguration'),
            ),
            fins: $this->parsePieces($el, 'fins'),
            gloves: $this->parsePieces($el, 'gloves'),
            knives: $this->parsePieces($el, 'knife'),
            lead: $this->parsePieces($el, 'lead'),
            lights: $this->parsePieces($el, 'light'),
            masks: $this->parsePieces($el, 'mask'),
            rebreathers: array_map(fn (\DOMElement $e): Rebreather => $this->parseRebreather($e), $this->children($el, 'rebreather')),
            regulators: $this->parsePieces($el, 'regulator'),
            scooters: $this->parsePieces($el, 'scooter'),
            suits: array_map(fn (\DOMElement $e): Suit => $this->parseSuit($e), $this->children($el, 'suit')),
            tanks: array_map(fn (\DOMElement $e): Tank => $this->parseTank($e), $this->children($el, 'tank')),
            variousPieces: $this->parsePieces($el, 'variouspieces'),
            videocameras: array_map(fn (\DOMElement $e): Videocamera => $this->parseVideocamera($e), $this->children($el, 'videocamera')),
            watches: $this->parsePieces($el, 'watch'),
        );
    }

    /** @return EquipmentPiece[] */
    private function parsePieces(\DOMElement $parent, string $elementName): array
    {
        return array_map(
            fn (\DOMElement $e): EquipmentPiece => $this->parsePiece($e),
            $this->children($parent, $elementName),
        );
    }

    private function parsePiece(\DOMElement $el): EquipmentPiece
    {
        $linkEl = $this->child($el, 'link');

        return new EquipmentPiece(
            id: $el->getAttribute('id'),
            name: $this->require($el, 'name'),
            aliasNames: $this->texts($el, 'aliasname'),
            linkRef: $linkEl?->getAttribute('ref'),
            manufacturer: $this->parseManufacturer($el),
            model: $this->text($el, 'model'),
            serialNumber: $this->text($el, 'serialnumber'),
            purchase: $this->parsePurchase($el),
            serviceInterval: $this->int($el, 'serviceinterval'),
            nextServiceDate: $this->parseEncapsulatedDateTime($el, 'nextservicedate'),
            notes: $this->parseNotes($el),
        );
    }

    private function parsePurchase(\DOMElement $parent): ?Purchase
    {
        $el = $this->child($parent, 'purchase');

        if ($el === null) {
            return null;
        }

        $datetimeStr = $this->text($el, 'datetime');

        return new Purchase(
            datetime: $datetimeStr !== null ? new \DateTimeImmutable($datetimeStr) : null,
            price: $this->parsePrice($el, 'price'),
            shop: $this->parseShop($el),
        );
    }

    private function parseShop(\DOMElement $parent): ?Shop
    {
        $el = $this->child($parent, 'shop');

        if ($el === null) {
            return null;
        }

        return new Shop(
            id: $el->getAttribute('id'),
            name: $this->require($el, 'name'),
            aliasNames: $this->texts($el, 'aliasname'),
            address: $this->parseAddress($el),
            contact: $this->parseContact($el),
            notes: $this->parseNotes($el),
        );
    }

    private function parseSuit(\DOMElement $el): Suit
    {
        $piece = $this->parsePiece($el);

        return new Suit(
            id: $piece->id,
            name: $piece->name,
            aliasNames: $piece->aliasNames,
            linkRef: $piece->linkRef,
            manufacturer: $piece->manufacturer,
            model: $piece->model,
            serialNumber: $piece->serialNumber,
            purchase: $piece->purchase,
            serviceInterval: $piece->serviceInterval,
            nextServiceDate: $piece->nextServiceDate,
            notes: $piece->notes,
            suitType: $this->parseEnum(SuitType::class, $el, 'suittype'),
        );
    }

    private function parseTank(\DOMElement $el): Tank
    {
        $piece = $this->parsePiece($el);

        return new Tank(
            id: $piece->id,
            name: $piece->name,
            volume: $this->float($el, 'tankvolume'),
            aliasNames: $piece->aliasNames,
            linkRef: $piece->linkRef,
            manufacturer: $piece->manufacturer,
            model: $piece->model,
            serialNumber: $piece->serialNumber,
            purchase: $piece->purchase,
            serviceInterval: $piece->serviceInterval,
            nextServiceDate: $piece->nextServiceDate,
            notes: $piece->notes,
            tankMaterial: $this->parseEnum(TankMaterial::class, $el, 'tankmaterial'),
        );
    }

    private function parseRebreather(\DOMElement $el): Rebreather
    {
        $piece = $this->parsePiece($el);

        return new Rebreather(
            id: $piece->id,
            name: $piece->name,
            aliasNames: $piece->aliasNames,
            linkRef: $piece->linkRef,
            manufacturer: $piece->manufacturer,
            model: $piece->model,
            serialNumber: $piece->serialNumber,
            purchase: $piece->purchase,
            serviceInterval: $piece->serviceInterval,
            nextServiceDate: $piece->nextServiceDate,
            notes: $piece->notes,
            o2Sensors: $this->parsePieces($el, 'o2sensor'),
        );
    }

    private function parseCamera(\DOMElement $el): Camera
    {
        return new Camera(
            id: $el->getAttribute('id'),
            bodies: $this->parsePieces($el, 'body'),
            lenses: $this->parsePieces($el, 'lens'),
            housings: $this->parsePieces($el, 'housing'),
            flashes: $this->parsePieces($el, 'flash'),
        );
    }

    private function parseVideocamera(\DOMElement $el): Videocamera
    {
        $bodyEl = $this->child($el, 'body');
        $lensEl = $this->child($el, 'lens');
        $housingEl = $this->child($el, 'housing');
        $lightEl = $this->child($el, 'light');

        return new Videocamera(
            id: $el->getAttribute('id'),
            body: $bodyEl !== null ? $this->parsePiece($bodyEl) : null,
            lens: $lensEl !== null ? $this->parsePiece($lensEl) : null,
            housing: $housingEl !== null ? $this->parsePiece($housingEl) : null,
            light: $lightEl !== null ? $this->parsePiece($lightEl) : null,
        );
    }

    private function parseEquipmentConfiguration(\DOMElement $el): EquipmentConfiguration
    {
        return new EquipmentConfiguration(
            id: $el->getAttribute('id'),
            name: $this->require($el, 'name'),
            aliasNames: $this->texts($el, 'aliasname'),
            linkRefs: array_map(
                static fn (\DOMElement $link): string => $link->getAttribute('ref'),
                $this->children($el, 'link'),
            ),
            notes: $this->parseNotes($el),
        );
    }

    /** @return Examination[] */
    private function parseExaminations(\DOMElement $personEl): array
    {
        $medicalEl = $this->child($personEl, 'medical');

        if ($medicalEl === null) {
            return [];
        }

        return array_map(
            fn (\DOMElement $e): Examination => $this->parseExamination($e),
            $this->children($medicalEl, 'examination'),
        );
    }

    private function parseExamination(\DOMElement $el): Examination
    {
        $doctorEl = $this->child($el, 'doctor');
        $linkEl = $this->child($el, 'link');
        $datetimeStr = $this->text($el, 'datetime');

        return new Examination(
            id: $el->getAttribute('id'),
            datetime: $datetimeStr !== null ? new \DateTimeImmutable($datetimeStr) : null,
            doctor: $doctorEl !== null ? $this->parseDoctor($doctorEl) : null,
            doctorRef: $doctorEl === null ? $linkEl?->getAttribute('ref') : null,
            result: $this->parseEnum(ExaminationResult::class, $el, 'examinationresult'),
            totalLungCapacity: $this->float($el, 'totallungcapacity'),
            vitalCapacity: $this->float($el, 'vitalcapacity'),
            notes: $this->parseNotes($el),
        );
    }

    private function parseDoctor(\DOMElement $el): Doctor
    {
        $personalEl = $this->child($el, 'personal');

        if ($personalEl === null) {
            throw new ParseException('Missing required <personal> element inside <doctor>.');
        }

        return new Doctor(
            id: $el->getAttribute('id'),
            personalData: $this->parsePersonalData($personalEl),
            address: $this->parseAddress($el),
            contact: $this->parseContact($el),
        );
    }

    /** @return Certification[] */
    private function parseCertifications(\DOMElement $personEl): array
    {
        $educationEl = $this->child($personEl, 'education');

        if ($educationEl === null) {
            return [];
        }

        return array_map(
            fn (\DOMElement $e): Certification => $this->parseCertification($e),
            $this->children($educationEl, 'certification'),
        );
    }

    private function parseCertification(\DOMElement $el): Certification
    {
        $instructorEl = $this->child($el, 'instructor');
        $issueDateStr = $this->parseEncapsulatedDateTime($el, 'issuedate');
        $validDateStr = $this->parseEncapsulatedDateTime($el, 'validdate');

        return new Certification(
            level: $this->text($el, 'level'),
            specialty: $this->text($el, 'specialty'),
            certificateNumber: $this->text($el, 'certificatenumber'),
            organization: $this->text($el, 'organization'),
            instructor: $instructorEl !== null ? $this->parseInstructor($instructorEl) : null,
            issueDate: $issueDateStr,
            validDate: $validDateStr,
        );
    }

    private function parseInstructor(\DOMElement $el): Instructor
    {
        $personalEl = $this->child($el, 'personal');

        if ($personalEl === null) {
            throw new ParseException('Missing required <personal> element inside <instructor>.');
        }

        return new Instructor(
            id: $el->getAttribute('id'),
            personalData: $this->parsePersonalData($personalEl),
            address: $this->parseAddress($el),
            contact: $this->parseContact($el),
            notes: $this->parseNotes($el),
        );
    }

    /** @return Permit[] */
    private function parsePermits(\DOMElement $personEl): array
    {
        $el = $this->child($personEl, 'divepermissions');

        if ($el === null) {
            return [];
        }

        return array_map(
            fn (\DOMElement $e): Permit => new Permit(
                name: $this->require($e, 'name'),
                aliasNames: $this->texts($e, 'aliasname'),
                region: $this->text($e, 'region'),
                issueDate: $this->parseEncapsulatedDateTime($e, 'issuedate'),
                validDate: $this->parseEncapsulatedDateTime($e, 'validdate'),
                notes: $this->parseNotes($e),
            ),
            $this->children($el, 'permit'),
        );
    }

    /** @return Insurance[] */
    private function parseInsurances(\DOMElement $personEl): array
    {
        $el = $this->child($personEl, 'diveinsurances');

        if ($el === null) {
            return [];
        }

        return array_map(
            fn (\DOMElement $e): Insurance => new Insurance(
                name: $this->require($e, 'name'),
                aliasNames: $this->texts($e, 'aliasname'),
                issueDate: $this->parseEncapsulatedDateTime($e, 'issuedate'),
                validDate: $this->parseEncapsulatedDateTime($e, 'validdate'),
                notes: $this->parseNotes($e),
            ),
            $this->children($el, 'insurance'),
        );
    }
}
