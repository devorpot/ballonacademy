<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        return Currency::select('code as value', 'label')->get();
    }

     public function options()
    {
        return Currency::orderBy('label')->get()->map(function ($currency) {
            return [
                'value' => $currency->id,
                'label' => "{$currency->code} - {$currency->label}"
            ];
        });
    }
}