<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electrónica', 'icon' => '💻', 'description' => 'Gadgets y dispositivos tecnológicos'],
            ['name' => 'Ropa',        'icon' => '👕', 'description' => 'Moda y accesorios de vestir'],
            ['name' => 'Hogar',       'icon' => '🏠', 'description' => 'Artículos para el hogar'],
            ['name' => 'Deportes',    'icon' => '⚽', 'description' => 'Equipamiento deportivo'],
            ['name' => 'Accesorios',  'icon' => '🎒', 'description' => 'Bolsos, mochilas y accesorios'],
        ];

        // ✅ IDEMPOTENTE (NO DUPLICA)
        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['name' => $cat['name']], // mejor aún: usar slug si lo tienes
                $cat
            );
        }

        // 🔥 Obtener categorías de forma segura
        $electronica = Category::where('name', 'Electrónica')->first()?->id;
        $ropa        = Category::where('name', 'Ropa')->first()?->id;
        $hogar       = Category::where('name', 'Hogar')->first()?->id;
        $accesorios  = Category::where('name', 'Accesorios')->first()?->id;

        // 🔧 Asignar productos (solo si existen categorías)
        if ($electronica) {
            Product::whereIn('name', [
                'Audífonos Bluetooth Pro',
                'Smartwatch Serie 5',
                'Cargador Inalámbrico 15W',
                'Teclado Mecánico RGB',
                'Mouse Ergonómico',
            ])->update(['category_id' => $electronica]);
        }

        if ($ropa) {
            Product::whereIn('name', [
                'Camiseta Premium Cotton',
                'Zapatillas Running X1',
            ])->update(['category_id' => $ropa]);
        }

        if ($hogar) {
            Product::whereIn('name', [
                'Cafetera Espresso Automática',
                'Lámpara LED Smart',
                'Botella Térmica 500ml',
            ])->update(['category_id' => $hogar]);
        }

        if ($accesorios) {
            Product::whereIn('name', [
                'Mochila Urban 30L',
            ])->update(['category_id' => $accesorios]);
        }
    }
}
