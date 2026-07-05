<?php

declare(strict_types=1);

namespace Kreitje\UddfGenerator;

use Kreitje\UddfGenerator\Spec\SpecVersionRegistry;

/**
 * Alias for the latest supported spec version's UddfGenerator
 * (currently Spec\V323\UddfGenerator). Kept as a real class_alias — not a
 * subclass or wrapper — so its constructor, generate(), and every property
 * behave identically to the versioned class with zero drift risk.
 */
class_alias(SpecVersionRegistry::generatorClass(), UddfGenerator::class);
