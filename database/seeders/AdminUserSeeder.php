<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
 
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Crear rol si no existe
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Crear usuario admin
        $user = User::firstOrCreate(
            ['email' => 'admin@ballonacademy.net'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin1234'),
            ]
        );

        // Asignar rol
        $user->assignRole($adminRole);
    }
}