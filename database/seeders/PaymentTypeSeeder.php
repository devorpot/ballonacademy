<?php

namespace Database\Seeders;
// database/seeders/PaymentTypeSeeder.php

use Illuminate\Database\Seeder;
use App\Models\PaymentType;

class PaymentTypeSeeder extends Seeder
{
    public function run(): void
    {
        PaymentType::insert([
            ['name' => 'Tarjeta de crédito'],
            ['name' => 'Tarjeta de débito'],
            ['name' => 'Transferencia bancaria'],
            ['name' => 'Efectivo'],
            ['name' => 'PayPal'],
            ['name' => 'Oxxo'],
            ['name' => 'GPay'],
            ['name' => 'Apple Pay'],
        ]);
    }
}
