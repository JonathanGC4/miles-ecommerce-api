<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TierSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tiers')->insert([
            [
                'name'       => 'Bronze',
                'min_miles'  => 0,
                'multiplier' => 1.0,
                'benefits'   => json_encode(['Acumulación base de millas']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Silver',
                'min_miles'  => 5000,
                'multiplier' => 1.5,
                'benefits'   => json_encode(['1.5x millas', 'Descuento 5% en compras']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Gold',
                'min_miles'  => 15000,
                'multiplier' => 2.0,
                'benefits'   => json_encode(['2x millas', 'Descuento 10% en compras', 'Soporte prioritario']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Platinum',
                'min_miles'  => 50000,
                'multiplier' => 3.0,
                'benefits'   => json_encode(['3x millas', 'Descuento 20% en compras', 'Acceso VIP', 'Gerente de cuenta']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
