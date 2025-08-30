<?php

namespace App\Support;

use App\Enums\Country;
use App\Enums\MXState;
use App\Enums\ARProvince;
use App\Enums\CLRegion;
use App\Enums\CODepartment;

final class SubdivisionsRegistry
{
    /** @return array<string> */
    public static function listFor(Country $country): array
    {
        return match ($country) {
            Country::MX => array_map(fn($c) => $c->value, MXState::cases()),
            Country::AR => array_map(fn($c) => $c->value, ARProvince::cases()),
            Country::CL => array_map(fn($c) => $c->value, CLRegion::cases()),
            Country::CO => array_map(fn($c) => $c->value, CODepartment::cases()),
            default => [],
        };
    }
}
