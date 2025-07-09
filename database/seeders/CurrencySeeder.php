<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Currency;
class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::insert([
            ['code' => 'MXN', 'label' => 'Peso Mexicano'],
            ['code' => 'USD', 'label' => 'Dólar Estadounidense'],
            ['code' => 'ARS', 'label' => 'Peso Argentino'],
            ['code' => 'EUR', 'label' => 'Euro'],
        ]);
    }
}
