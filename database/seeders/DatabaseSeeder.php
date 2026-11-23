<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['Administrador Innova', 'admin@innovatech.test', 'admin'],
            ['Administrador Operaciones', 'operaciones@innovatech.test', 'admin'],
            ['Cliente Demo', 'cliente@innovatech.test', 'customer'],
            ['Cliente Compras', 'compras@innovatech.test', 'customer'],
            ['Cliente Soporte', 'soporte@innovatech.test', 'customer'],
            ['Cliente Mayorista', 'mayorista@innovatech.test', 'customer'],
        ];

        foreach ($users as [$name, $email, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => $role,
                ]
            );
        }

        $this->call(BeerCatalogSeeder::class);
    }
}
