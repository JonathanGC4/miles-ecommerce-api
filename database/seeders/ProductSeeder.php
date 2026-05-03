<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Electrónicos
            [
                'name'             => 'Audífonos Bluetooth Pro',
                'category_id'      => Category::where('name', 'Electrónica')->first()?->id,
                'description'      => 'Audífonos inalámbricos con cancelación de ruido activa y 30 horas de batería.',
                'price'            => 89.99,
                'stock'            => 50,
                'miles_per_dollar' => 10,
                'active'           => true,
            ],
            [
                'name'             => 'Smartwatch Serie 5',
                'category_id'      => Category::where('name', 'Electrónica')->first()?->id,
                'description'      => 'Reloj inteligente con monitor de salud, GPS y resistencia al agua.',
                'price'            => 199.99,
                'stock'            => 30,
                'miles_per_dollar' => 12,
                'active'           => true,
            ],
            [
                'name'             => 'Cargador Inalámbrico 15W',
                'category_id'      => Category::where('name', 'Electrónica')->first()?->id,
                'description'      => 'Cargador rápido compatible con todos los dispositivos Qi.',
                'price'            => 29.99,
                'stock'            => 100,
                'miles_per_dollar' => 8,
                'active'           => true,
            ],
            [
                'name'             => 'Teclado Mecánico RGB',
                'category_id'      => Category::where('name', 'Electrónica')->first()?->id,
                'description'      => 'Teclado gaming con switches Cherry MX e iluminación RGB personalizable.',
                'price'            => 129.99,
                'stock'            => 25,
                'miles_per_dollar' => 10,
                'active'           => true,
            ],
            [
                'name'             => 'Mouse Ergonómico',
                'category_id'      => Category::where('name', 'Electrónica')->first()?->id,
                'description'      => 'Mouse inalámbrico con diseño ergonómico y batería de larga duración.',
                'price'            => 49.99,
                'stock'            => 75,
                'miles_per_dollar' => 8,
                'active'           => true,
            ],

            // Ropa
            [
                'name'             => 'Camiseta Premium Cotton',
                'category_id'      => Category::where('name', 'Ropa')->first()?->id,
                'description'      => 'Camiseta 100% algodón orgánico, disponible en varios colores.',
                'price'            => 24.99,
                'stock'            => 200,
                'miles_per_dollar' => 5,
                'active'           => true,
            ],
            [
                'name'             => 'Zapatillas Running X1',
                'category_id'      => Category::where('name', 'Ropa')->first()?->id,
                'description'      => 'Zapatillas ligeras con suela de amortiguación para running diario.',
                'price'            => 79.99,
                'stock'            => 60,
                'miles_per_dollar' => 10,
                'active'           => true,
            ],
            [
                'name'             => 'Mochila Urban 30L',
                'category_id'      => Category::where('name', 'Accesorios')->first()?->id,
                'description'      => 'Mochila resistente al agua con compartimento para laptop de 15 pulgadas.',
                'price'            => 59.99,
                'stock'            => 45,
                'miles_per_dollar' => 8,
                'active'           => true,
            ],

            // Hogar
            [
                'name'             => 'Cafetera Espresso Automática',
                'category_id'      => Category::where('name', 'Hogar')->first()?->id,
                'description'      => 'Cafetera automática con molinillo integrado y pantalla táctil.',
                'price'            => 249.99,
                'stock'            => 20,
                'miles_per_dollar' => 15,
                'active'           => true,
            ],
            [
                'name'             => 'Lámpara LED Smart',
                'category_id'      => Category::where('name', 'Hogar')->first()?->id,
                'description'      => 'Lámpara inteligente controlable por app con millones de colores.',
                'price'            => 39.99,
                'stock'            => 80,
                'miles_per_dollar' => 8,
                'active'           => true,
            ],
            [
                'name'             => 'Botella Térmica 500ml',
                'category_id'      => Category::where('name', 'Hogar')->first()?->id,
                'description'      => 'Botella de acero inoxidable que mantiene la temperatura 24 horas.',
                'price'            => 19.99,
                'stock'            => 150,
                'miles_per_dollar' => 5,
                'active'           => true,
            ],

            // Producto inactivo para pruebas
            [
                'name'             => 'Tablet Descontinuada',
                'category_id'      => Category::where('name', 'Electrónica')->first()?->id,
                'description'      => 'Producto fuera de catálogo.',
                'price'            => 149.99,
                'stock'            => 0,
                'miles_per_dollar' => 10,
                'active'           => false,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
