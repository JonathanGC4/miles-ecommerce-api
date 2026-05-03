<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $clients  = User::where('role', 'client')->get();
        $products = Product::where('active', true)->get();

        $ordersData = [
            // Ana Bronze — 2 órdenes
            [
                'user_email' => 'ana@miles.com',
                'items'      => [
                    ['product_index' => 0, 'quantity' => 1], // Audífonos
                    ['product_index' => 2, 'quantity' => 2], // Cargador
                ],
                'days_ago' => 10,
            ],
            [
                'user_email' => 'ana@miles.com',
                'items'      => [
                    ['product_index' => 5, 'quantity' => 3], // Camisetas
                ],
                'days_ago' => 3,
            ],

            // Pedro Silver — 3 órdenes
            [
                'user_email' => 'pedro@miles.com',
                'items'      => [
                    ['product_index' => 1, 'quantity' => 1], // Smartwatch
                ],
                'days_ago' => 20,
            ],
            [
                'user_email' => 'pedro@miles.com',
                'items'      => [
                    ['product_index' => 3, 'quantity' => 1], // Teclado
                    ['product_index' => 4, 'quantity' => 1], // Mouse
                ],
                'days_ago' => 12,
            ],
            [
                'user_email' => 'pedro@miles.com',
                'items'      => [
                    ['product_index' => 6, 'quantity' => 1], // Zapatillas
                    ['product_index' => 10, 'quantity' => 2], // Botellas
                ],
                'days_ago' => 2,
            ],

            // María Gold — 2 órdenes
            [
                'user_email' => 'maria@miles.com',
                'items'      => [
                    ['product_index' => 8, 'quantity' => 1], // Cafetera
                    ['product_index' => 9, 'quantity' => 2], // Lámparas
                ],
                'days_ago' => 15,
            ],
            [
                'user_email' => 'maria@miles.com',
                'items'      => [
                    ['product_index' => 1, 'quantity' => 1], // Smartwatch
                    ['product_index' => 7, 'quantity' => 1], // Mochila
                ],
                'days_ago' => 5,
            ],

            // Juan Platinum — 3 órdenes
            [
                'user_email' => 'juan@miles.com',
                'items'      => [
                    ['product_index' => 1, 'quantity' => 2], // Smartwatch x2
                    ['product_index' => 8, 'quantity' => 1], // Cafetera
                ],
                'days_ago' => 30,
            ],
            [
                'user_email' => 'juan@miles.com',
                'items'      => [
                    ['product_index' => 3, 'quantity' => 2], // Teclados
                    ['product_index' => 4, 'quantity' => 2], // Mouse x2
                    ['product_index' => 0, 'quantity' => 1], // Audífonos
                ],
                'days_ago' => 14,
            ],
            [
                'user_email' => 'juan@miles.com',
                'items'      => [
                    ['product_index' => 9, 'quantity' => 3], // Lámparas
                    ['product_index' => 6, 'quantity' => 2], // Zapatillas
                ],
                'days_ago' => 1,
            ],
        ];

        foreach ($ordersData as $orderData) {
            $user    = User::where('email', $orderData['user_email'])->first();
            $account = $user->milesAccount;

            // Calcular total y millas
            $total       = 0;
            $milesEarned = 0;
            $multiplier  = $account->getMultiplier();
            $itemsToCreate = [];

            foreach ($orderData['items'] as $itemData) {
                $product  = $products[$itemData['product_index']];
                $quantity = $itemData['quantity'];
                $subtotal = $product->price * $quantity;

                $total       += $subtotal;
                $milesEarned += $product->calculateMiles($multiplier) * $quantity;

                $itemsToCreate[] = [
                    'product'  => $product,
                    'quantity' => $quantity,
                    'price'    => $product->price,
                ];
            }

            // Crear orden
            $order = Order::create([
                'user_id'     => $user->id,
                'total'       => round($total, 2),
                'miles_earned'=> $milesEarned,
                'status'      => 'completed',
                'created_at'  => Carbon::now()->subDays($orderData['days_ago']),
                'updated_at'  => Carbon::now()->subDays($orderData['days_ago']),
            ]);

            // Crear items
            foreach ($itemsToCreate as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ]);

                // Descontar stock
                $item['product']->decrement('stock', $item['quantity']);
            }

            // Registrar transacción
            Transaction::create([
                'user_id'     => $user->id,
                'type'        => 'purchase',
                'amount'      => $milesEarned,
                'description' => "Compra #{$order->id} — {$milesEarned} millas ganadas",
                'order_id'    => $order->id,
                'created_at'  => $order->created_at,
                'updated_at'  => $order->updated_at,
            ]);
        }
    }
}
