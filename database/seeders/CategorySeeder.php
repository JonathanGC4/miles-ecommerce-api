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
            ['name' => 'Electrónica',  'icon' => '💻', 'description' => 'Gadgets y dispositivos tecnológicos'],
            ['name' => 'Ropa',         'icon' => '👕', 'description' => 'Moda y accesorios de vestir'],
            ['name' => 'Hogar',        'icon' => '🏠', 'description' => 'Artículos para el hogar'],
            ['name' => 'Deportes',     'icon' => '⚽', 'description' => 'Equipamiento deportivo'],
            ['name' => 'Accesorios',   'icon' => '🎒', 'description' => 'Bolsos, mochilas y accesorios'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Asignar categorías a productos existentes
        $electronica = Category::where('name', 'Electrónica')->first();
        $ropa        = Category::where('name', 'Ropa')->first();
        $hogar       = Category::where('name', 'Hogar')->first();
        $accesorios  = Category::where('name', 'Accesorios')->first();

        Product::whereIn('name', [
            'Audífonos Bluetooth Pro',
            'Smartwatch Serie 5',
            'Cargador Inalámbrico 15W',
            'Teclado Mecánico RGB',
            'Mouse Ergonómico',
        ])->update(['category_id' => $electronica->id]);

        Product::whereIn('name', [
            'Camiseta Premium Cotton',
            'Zapatillas Running X1',
        ])->update(['category_id' => $ropa->id]);

        Product::whereIn('name', [
            'Cafetera Espresso Automática',
            'Lámpara LED Smart',
            'Botella Térmica 500ml',
        ])->update(['category_id' => $hogar->id]);

        Product::whereIn('name', [
            'Mochila Urban 30L',
        ])->update(['category_id' => $accesorios->id]);
    }
}
