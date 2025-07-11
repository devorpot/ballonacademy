<?php

namespace Database\Seeders;

// database/seeders/PaymentStatusSeeder.php

use Illuminate\Database\Seeder;
use App\Models\PaymentStatus;

class PaymentStatusSeeder extends Seeder
{
    public function run(): void
    {
        PaymentStatus::insert([
            ['name' => 'Pagado'],
            ['name' => 'Pendiente'],
            ['name' => 'Reembolsado'],
            ['name' => 'Cancelado'],
        ]);
    }
}