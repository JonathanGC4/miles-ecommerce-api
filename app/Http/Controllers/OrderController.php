<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Tier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    const MILES_TO_DOLLAR     = 100;  // 100 millas = $1
    const MAX_DISCOUNT_PERCENT = 0.30; // máximo 30% de descuento

    // POST /checkout
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method'           => 'required|in:card,cash',
            'discount_miles'           => 'nullable|integer|min:0',
            'shipping_address'         => 'required|array',
            'shipping_address.name'    => 'required|string',
            'shipping_address.address' => 'required|string',
            'shipping_address.city'    => 'required|string',
            'shipping_address.country' => 'required|string',
            'shipping_address.phone'   => 'required|string',
            // Campos de tarjeta (solo si pago es con tarjeta)
            'card_number'              => 'required_if:payment_method,card|nullable|string',
            'card_expiry'              => 'required_if:payment_method,card|nullable|string',
            'card_cvv'                 => 'required_if:payment_method,card|nullable|string',
        ]);

        $user    = $request->user();
        $items   = $user->cartItems()->with('product')->get();
        $account = $user->milesAccount;

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Tu carrito está vacío.'], 422);
        }

        // Validar stock
        foreach ($items as $item) {
            if (!$item->product->active) {
                return response()->json([
                    'message' => "El producto '{$item->product->name}' ya no está disponible.",
                ], 422);
            }
            if (!$item->product->hasStock($item->quantity)) {
                return response()->json([
                    'message' => "Stock insuficiente para '{$item->product->name}'.",
                    'data'    => ['available' => $item->product->stock],
                ], 422);
            }
        }

        // Calcular total
        $subtotal = $items->sum(fn($item) => $item->subtotal());

        // Calcular descuento por millas
        $discountMiles  = (int) ($request->discount_miles ?? 0);
        $discountAmount = 0;

        if ($discountMiles > 0) {
            // Validar saldo suficiente
            if (!$account->hasSufficientBalance($discountMiles)) {
                return response()->json([
                    'message'   => 'No tienes suficientes millas para ese descuento.',
                    'balance'   => $account->balance,
                    'requested' => $discountMiles,
                ], 422);
            }

            // Calcular descuento
            $maxDiscount    = round($subtotal * self::MAX_DISCOUNT_PERCENT, 2);
            $requestedDiscount = round($discountMiles / self::MILES_TO_DOLLAR, 2);
            $discountAmount = min($requestedDiscount, $maxDiscount);

            // Ajustar millas si el descuento fue limitado al 30%
            $discountMiles = (int) ceil($discountAmount * self::MILES_TO_DOLLAR);
        }

        $finalTotal = max(0, round($subtotal - $discountAmount, 2));

        try {
            $order = DB::transaction(function () use (
                $user, $items, $account, $subtotal,
                $discountMiles, $discountAmount, $finalTotal, $request
            ) {
                $multiplier  = $account->getMultiplier();

                // Millas ganadas sobre el total final (no el subtotal)
                $milesEarned = $items->sum(function ($item) use ($multiplier) {
                    return $item->product->calculateMiles($multiplier) * $item->quantity;
                });

                // Si hay descuento por millas, las millas ganadas se calculan sobre el total final
                if ($finalTotal < $subtotal) {
                    $ratio       = $finalTotal / $subtotal;
                    $milesEarned = (int) round($milesEarned * $ratio);
                }

                // 1. Crear orden
                $order = Order::create([
                    'user_id'          => $user->id,
                    'total'            => $subtotal,
                    'final_total'      => $finalTotal,
                    'miles_earned'     => $milesEarned,
                    'discount_miles'   => $discountMiles,
                    'discount_amount'  => $discountAmount,
                    'status'           => 'completed',
                    'payment_method'   => $request->payment_method,
                    'shipping_address' => $request->shipping_address,
                ]);

                // 2. Crear items y descontar stock
                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                        'price'      => $item->product->price,
                    ]);
                    $item->product->decrement('stock', $item->quantity);
                }

                // 3. Descontar millas usadas como descuento
                if ($discountMiles > 0) {
                    $account->decrement('balance', $discountMiles);

                    Transaction::create([
                        'user_id'     => $user->id,
                        'type'        => 'redeem',
                        'amount'      => $discountMiles,
                        'description' => "Descuento aplicado en orden #{$order->id}",
                        'order_id'    => $order->id,
                    ]);
                }

                // 4. Acreditar millas ganadas
                $account->increment('balance', $milesEarned);
                $account->increment('lifetime_miles', $milesEarned);

                // 5. Verificar subida de tier
                $newTier = Tier::getForMiles($account->fresh()->lifetime_miles);
                if ($newTier->id !== $account->tier_id) {
                    $account->update(['tier_id' => $newTier->id]);
                }

                // 6. Registrar transacción de compra
                Transaction::create([
                    'user_id'     => $user->id,
                    'type'        => 'purchase',
                    'amount'      => $milesEarned,
                    'description' => "Compra #{$order->id} — {$milesEarned} millas ganadas",
                    'order_id'    => $order->id,
                ]);

                // 7. Vaciar carrito
                $user->cartItems()->delete();

                return $order;
            });

            $account->refresh()->load('tier');

            return response()->json([
                'message' => '¡Compra realizada exitosamente!',
                'data'    => [
                    'order'          => $order->load('items.product'),
                    'subtotal'       => $subtotal,
                    'discount_miles' => $discountMiles,
                    'discount_amount'=> $discountAmount,
                    'final_total'    => $finalTotal,
                    'miles_earned'   => $order->miles_earned,
                    'balance'        => $account->balance,
                    'lifetime_miles' => $account->lifetime_miles,
                    'tier'           => $account->tier->name,
                    'next_tier'      => $this->getNextTier($account->lifetime_miles),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar la compra.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // POST /miles/redeem-product — Canjear producto gratis con millas
    public function redeemProduct(Request $request)
    {
        $request->validate([
            'product_id'       => 'required|exists:products,id',
            'shipping_address' => 'required|array',
            'shipping_address.name'    => 'required|string',
            'shipping_address.address' => 'required|string',
            'shipping_address.city'    => 'required|string',
            'shipping_address.country' => 'required|string',
            'shipping_address.phone'   => 'required|string',
        ]);

        $user    = $request->user();
        $account = $user->milesAccount;
        $product = Product::findOrFail($request->product_id);

        if (!$product->active || !$product->hasStock(1)) {
            return response()->json([
                'message' => 'Producto no disponible.',
            ], 422);
        }

        // Costo en millas = precio × miles_per_dollar × 10
        $milesCost = (int) ($product->price * $product->miles_per_dollar * 10);

        if (!$account->hasSufficientBalance($milesCost)) {
            return response()->json([
                'message'   => 'No tienes suficientes millas para canjear este producto.',
                'required'  => $milesCost,
                'balance'   => $account->balance,
                'missing'   => $milesCost - $account->balance,
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($user, $account, $product, $milesCost, $request) {
                // Crear orden con total $0
                $order = Order::create([
                    'user_id'          => $user->id,
                    'total'            => $product->price,
                    'final_total'      => 0,
                    'miles_earned'     => 0,
                    'discount_miles'   => $milesCost,
                    'discount_amount'  => $product->price,
                    'status'           => 'completed',
                    'payment_method'   => 'miles',
                    'shipping_address' => $request->shipping_address,
                ]);

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => 1,
                    'price'      => $product->price,
                ]);

                $product->decrement('stock', 1);
                $account->decrement('balance', $milesCost);

                Transaction::create([
                    'user_id'     => $user->id,
                    'type'        => 'redeem',
                    'amount'      => $milesCost,
                    'description' => "Canje de producto: {$product->name}",
                    'order_id'    => $order->id,
                ]);

                return $order;
            });

            $account->refresh();

            return response()->json([
                'message' => '¡Producto canjeado exitosamente!',
                'data'    => [
                    'order'      => $order->load('items.product'),
                    'miles_used' => $milesCost,
                    'balance'    => $account->balance,
                    'product'    => $product->name,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al canjear el producto.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // GET /checkout/preview — Preview del checkout con descuento
    public function preview(Request $request)
    {
        $request->validate([
            'discount_miles' => 'nullable|integer|min:0',
        ]);

        $user    = $request->user();
        $items   = $user->cartItems()->with('product')->get();
        $account = $user->milesAccount->load('tier');

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Carrito vacío.'], 422);
        }

        $subtotal       = $items->sum(fn($item) => $item->subtotal());
        $discountMiles  = (int) ($request->discount_miles ?? 0);
        $discountAmount = 0;
        $maxDiscount    = round($subtotal * self::MAX_DISCOUNT_PERCENT, 2);
        $maxMiles       = (int) ceil($maxDiscount * self::MILES_TO_DOLLAR);

        if ($discountMiles > 0) {
            $requestedDiscount = round($discountMiles / self::MILES_TO_DOLLAR, 2);
            $discountAmount    = min($requestedDiscount, $maxDiscount);
        }

        $finalTotal  = max(0, round($subtotal - $discountAmount, 2));
        $multiplier  = $account->getMultiplier();
        $milesEarned = $items->sum(function ($item) use ($multiplier) {
            return $item->product->calculateMiles($multiplier) * $item->quantity;
        });

        if ($finalTotal < $subtotal && $subtotal > 0) {
            $milesEarned = (int) round($milesEarned * ($finalTotal / $subtotal));
        }

        return response()->json([
            'data' => [
                'subtotal'        => $subtotal,
                'max_discount'    => $maxDiscount,
                'max_miles'       => $maxMiles,
                'discount_miles'  => $discountMiles,
                'discount_amount' => $discountAmount,
                'final_total'     => $finalTotal,
                'miles_earned'    => $milesEarned,
                'balance'         => $account->balance,
                'tier'            => $account->tier->name,
                'multiplier'      => $multiplier,
            ],
        ]);
    }

    // GET /orders
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'message'      => 'Órdenes obtenidas correctamente',
            'data'         => $orders->items(),
            'total'        => $orders->total(),
            'current_page' => $orders->currentPage(),
            'last_page'    => $orders->lastPage(),
        ]);
    }

    // GET /orders/{id}
    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        return response()->json([
            'message' => 'Orden obtenida correctamente',
            'data'    => $order->load('items.product'),
        ]);
    }

    private function getNextTier(int $lifetimeMiles): ?array
    {
        $next = Tier::where('min_miles', '>', $lifetimeMiles)
            ->orderBy('min_miles')->first();

        if (!$next) return null;

        return [
            'name'      => $next->name,
            'min_miles' => $next->min_miles,
            'missing'   => $next->min_miles - $lifetimeMiles,
        ];
    }
}
