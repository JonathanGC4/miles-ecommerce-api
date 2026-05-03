<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Http\Requests\AddToCartRequest;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // GET /cart — Ver carrito
    public function index(Request $request)
    {
        $items = $request->user()
            ->cartItems()
            ->with('product')
            ->get();

        $subtotal     = $items->sum(fn($item) => $item->subtotal());
        $account      = $request->user()->milesAccount->load('tier');
        $multiplier   = $account->getMultiplier();

        // Calcular millas que ganaría con esta compra
        $estimatedMiles = $items->sum(function ($item) use ($multiplier) {
            return $item->product->calculateMiles($multiplier) * $item->quantity;
        });

        return response()->json([
            'message' => 'Carrito obtenido correctamente',
            'data'    => [
                'items'          => $items,
                'subtotal'       => round($subtotal, 2),
                'estimated_miles'=> $estimatedMiles,
                'tier'           => $account->tier->name,
                'multiplier'     => $multiplier,
            ],
        ]);
    }

    // POST /cart — Agregar producto
    public function store(AddToCartRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        if (! $product->active) {
            return response()->json([
                'message' => 'El producto no está disponible.',
            ], 422);
        }

        if (! $product->hasStock($request->quantity)) {
            return response()->json([
                'message' => 'Stock insuficiente.',
                'data'    => ['available' => $product->stock],
            ], 422);
        }

        // Si ya existe en el carrito, actualizar cantidad
        $cartItem = CartItem::updateOrCreate(
            [
                'user_id'    => $request->user()->id,
                'product_id' => $request->product_id,
            ],
            ['quantity' => $request->quantity]
        );

        return response()->json([
            'message' => 'Producto agregado al carrito',
            'data'    => $cartItem->load('product'),
        ], 201);
    }

    // DELETE /cart/{id} — Quitar del carrito
    public function destroy(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para eliminar este item.',
            ], 403);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Producto eliminado del carrito',
        ]);
    }

    // DELETE /cart — Vaciar carrito
    public function clear(Request $request)
    {
        $request->user()->cartItems()->delete();

        return response()->json([
            'message' => 'Carrito vaciado correctamente',
        ]);
    }
}
