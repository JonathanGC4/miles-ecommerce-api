<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TierSeeder::class,    // 1. Tiers primero
            CategorySeeder::class, // 2. Categorías
            UserSeeder::class,    // 3. Usuarios con cuentas de millas
            ProductSeeder::class, // 4. Productos
            OrderSeeder::class,   // 5. Órdenes con historial
        ]);
    }
}
