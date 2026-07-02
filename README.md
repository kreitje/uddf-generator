# kreitje/uddf-generator

A PHP library for generating and parsing [UDDF](https://www.streit.cc/resources/UDDF/v3.2.3/en/index.html) (Universal Dive Data Format) XML files. The entire UDDF document is modelled as a tree of typed PHP objects — build the tree, call `generate()`, get valid UDDF 3.2.3 XML.

## Requirements

- PHP 8.2+
- `ext-dom`

## Installation

```bash
composer require kreitje/uddf-generator
```

## Quick start

```php
use Kreitje\UddfGenerator\UddfGenerator;
use Kreitje\UddfGenerator\Generator\Generator;
use Kreitje\UddfGenerator\Gas\{GasDefinitions, Mix};
use Kreitje\UddfGenerator\ProfileData\{ProfileData, RepetitionGroup, Dive, InformationBeforeDive, Waypoint};

$uddf = new UddfGenerator(
    generator: new Generator(name: 'My Dive App', version: '1.0.0'),
    gasDefinitions: new GasDefinitions([Mix::air()]),
    profileData: new ProfileData([
        new RepetitionGroup(id: 'rg1', dives: [
            new Dive(
                id: 'dive_1',
                informationBeforeDive: new InformationBeforeDive(
                    datetime: new DateTimeImmutable('2024-06-15T09:00:00'),
                    diveNumber: 42,
                ),
                samples: [
                    new Waypoint(depth: 0.0,  diveTime: 0,    mixChangeRef: 'air'),
                    new Waypoint(depth: 18.0, diveTime: 300,  temperature: 296.15, tankPressure: 180.0),
                    new Waypoint(depth: 18.0, diveTime: 1500, temperature: 295.15, tankPressure: 80.0),
                    new Waypoint(depth: 0.0,  diveTime: 1800, tankPressure: 50.0),
                ],
            ),
        ]),
    ]),
);

$xml = $uddf->generate(); // returns a formatted XML string
```

## Generating UDDF XML

### `UddfGenerator`

The root class. All sections are optional except `generator`.

```php
new UddfGenerator(
    generator: Generator,            // required
    diver: ?Diver,                   // optional — owner/personal info
    diveSites: DiveSite[],           // optional — array of sites
    gasDefinitions: ?GasDefinitions, // optional — breathing gas mixes
    profileData: ?ProfileData,       // optional — dive records
);
```

Call `generate()` to get a UTF-8 encoded, pretty-printed XML string conforming to UDDF 3.2.3.

---

### Generator

Identifies the software that produced the file. The `datetime` defaults to now if omitted.

```php
use Kreitje\UddfGenerator\Generator\{Generator, Manufacturer};

new Generator(
    name: 'My App',
    version: '1.0.0',
    datetime: new DateTimeImmutable('2024-01-01T12:00:00'), // optional
    manufacturer: new Manufacturer(                          // optional
        id: 'acme',
        name: 'Acme Diving Co.',
        email: 'support@acme.example',
        phone: '+61400000000',
        address: '1 Reef Street, Sydney',
    ),
);
```

---

### Dive sites

```php
use Kreitje\UddfGenerator\DiveSite\{DiveSite, Geography};

$sites = [
    new DiveSite(
        id: 'site_gbr',                    // referenced by dives via diveSiteRef
        name: 'Great Barrier Reef',
        geography: new Geography(
            location: 'Queensland, Australia',
            latitude: -18.2871,
            longitude: 147.6992,
            country: 'AU',
        ),
        notes: 'UNESCO World Heritage Site',
    ),
];

new UddfGenerator(generator: $gen, diveSites: $sites, ...);
```

---

### Gas definitions

```php
use Kreitje\UddfGenerator\Gas\{GasDefinitions, Mix};

$gases = new GasDefinitions([
    Mix::air(),                         // O2 21%, N2 79%
    Mix::nitrox('ean32', 0.32),         // O2 32%, N2 68%
    Mix::trimix('tx21/35', 0.21, 0.35), // O2 21%, He 35%, N2 44%

    // Or build a mix manually:
    new Mix(id: 'custom', name: 'Custom', o2: 0.50, n2: 0.50),
]);
```

The `id` on each mix is used to cross-reference from waypoints via `mixChangeRef`.

---

### Dive profiles

```php
use Kreitje\UddfGenerator\ProfileData\{
    ProfileData, RepetitionGroup, Dive,
    InformationBeforeDive, InformationAfterDive, Waypoint,
};

$profileData = new ProfileData([
    new RepetitionGroup(id: 'rg_morning', dives: [

        new Dive(
            id: 'dive_042',
            informationBeforeDive: new InformationBeforeDive(
                datetime: new DateTimeImmutable('2024-06-15T09:00:00'),
                diveNumber: 42,
                diveSiteRef: 'site_gbr', // references a DiveSite id
                notes: 'Saw a manta ray',
            ),
            samples: [
                // depth in metres · diveTime in seconds · temperature in Kelvin · tankPressure in bar
                new Waypoint(depth: 0.0,  diveTime: 0,    mixChangeRef: 'air'),
                new Waypoint(depth: 20.0, diveTime: 300,  temperature: 299.15, tankPressure: 190.0),
                new Waypoint(depth: 20.0, diveTime: 1500, temperature: 297.15, tankPressure: 70.0),
                new Waypoint(depth: 5.0,  diveTime: 1740, temperature: 298.15, tankPressure: 55.0),
                new Waypoint(depth: 0.0,  diveTime: 1860, tankPressure: 50.0),
            ],
            // informationAfterDive is auto-computed from samples when omitted
        ),

    ]),
]);
```

#### `InformationAfterDive` — explicit or automatic

When `informationAfterDive` is omitted from a `Dive`, it is automatically computed from the waypoints (greatest depth, total duration, average depth). You can also provide it explicitly or compute it yourself:

```php
// Explicit
new Dive(
    id: 'dive_042',
    informationBeforeDive: $before,
    samples: $waypoints,
    informationAfterDive: new InformationAfterDive(
        greatestDepth: 20.0, // metres
        diveDuration: 1860,  // seconds
        averageDepth: 12.5,  // metres, optional
    ),
);

// Computed from waypoints
$after = InformationAfterDive::fromWaypoints(...$waypoints);
```

---

### Diver

```php
use Kreitje\UddfGenerator\Diver\{Diver, Owner, PersonalData, Equipment, Tank};

$diver = new Diver(
    owner: new Owner(
        id: 'owner',
        personalData: new PersonalData(
            firstName: 'Jane',
            lastName: 'Doe',
            birthdate: new DateTimeImmutable('1990-05-15'),
            sex: 'female',
        ),
        equipment: new Equipment(tanks: [
            new Tank(id: 'tank_1', name: 'Alu80', volume: 11.1, workpressure: 207.0),
        ]),
    ),
);
```

---

## Parsing UDDF XML

`UddfParser` reads any UDDF 3.2.3 XML (with or without a default namespace declaration) and returns a fully populated `UddfGenerator` object tree.

```php
use Kreitje\UddfGenerator\UddfParser;
use Kreitje\UddfGenerator\ParseException;

$parser = new UddfParser();

// Parse from a string
$uddf = $parser->parse($xmlString);

// Parse from a file
$uddf = $parser->parseFile('/path/to/dive-log.uddf');

// Access the parsed data
echo $uddf->generator->name;
echo $uddf->profileData?->repetitionGroups[0]->dives[0]->samples[1]->depth;

// Re-generate XML from the parsed data
$xml = $uddf->generate();
```

`ParseException` is thrown for invalid XML, a non-`<uddf>` root element, or missing required child elements (`<name>`, `<version>`).

---

## Unit conventions

| Property | Unit |
|---|---|
| `Waypoint::$depth` | Metres |
| `Waypoint::$diveTime` | Seconds from dive start |
| `Waypoint::$temperature` | Kelvin (273.15 + °C) |
| `Waypoint::$tankPressure` | Bar |
| `Mix::$o2` / `$n2` / `$he` | Fraction 0–1 (e.g. `0.21` for 21%) |
| `InformationAfterDive::$greatestDepth` | Metres |
| `InformationAfterDive::$diveDuration` | Seconds |
| `Geography::$latitude` / `$longitude` | Decimal degrees |
| `Tank::$volume` | Litres |
| `Tank::$workpressure` | Bar |

---

## Class reference

```
UddfGenerator
├── Generator\Generator
│   └── Generator\Manufacturer
├── Diver\Diver
│   └── Diver\Owner
│       ├── Diver\PersonalData
│       └── Diver\Equipment
│           └── Diver\Tank[]
├── DiveSite\DiveSite[]
│   └── DiveSite\Geography
├── Gas\GasDefinitions
│   └── Gas\Mix[]
└── ProfileData\ProfileData
    └── ProfileData\RepetitionGroup[]
        └── ProfileData\Dive[]
            ├── ProfileData\InformationBeforeDive
            ├── ProfileData\Waypoint[]
            └── ProfileData\InformationAfterDive
```

All classes are `final` with `readonly` constructor-promoted properties. Every class implements `XmlSerializable` (`toXml(\DOMDocument): \DOMElement`).

## Running tests

```bash
composer test
```
