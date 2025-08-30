<?php

namespace App\Http\Controllers\Api;

use App\Enums\Country;
use App\Support\SubdivisionsRegistry;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class LocationsController extends Controller
{
    public function countries()
    {
        return response()->json(Country::options());
    }

    public function states(string $country)
    {
        // valida que sea un país del enum
        validator(['country'=>$country], [
            'country' => [ 'required', Rule::in(array_map(fn($c)=>$c->value, Country::cases())) ]
        ])->validate();

        $countryEnum = Country::from($country);
        $states = SubdivisionsRegistry::listFor($countryEnum);

        // para select: [{value:'Jalisco',label:'Jalisco'}, ...]
        return response()->json(array_map(fn($s)=>['value'=>$s,'label'=>$s], $states));
    }
}
