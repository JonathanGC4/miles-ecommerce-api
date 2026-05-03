<?php

namespace App\Http\Controllers;

use App\Models\Tier;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MilesController extends Controller
{
    // GET /miles — Saldo actual + tier del usuario autenticado
    public function balance(Request $request)
    {
        $account = $request->user()->milesAccount->load('tier');

        return response()->json([
            'message' => 'Saldo obtenido correctamente',
            'data'    => [
                'balance'        => $account->balance,
                'lifetime_miles' => $account->lifetime_miles,
                'tier'           => [
                    'name'        => $account->tier->name,
                    'multiplier'  => $account->tier->multiplier,
                    'benefits'    => $account->tier->benefits,
                    'next_tier'   => $this->getNextTier($account->lifetime_miles),
                ],
            ],
        ]);
    }

    // POST /miles/earn — Acreditar millas (seller o admin)
    public function earn(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'amount'      => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $client = \App\Models\User::findOrFail($request->user_id);

        if (! $client->isClient()) {
            return response()->json([
                'message' => 'Solo puedes acreditar millas a clientes.',
            ], 422);
        }

        $account = $client->milesAccount;

        try {
            DB::transaction(function () use ($account, $request, $client) {
                // Calcular millas con multiplicador del tier actual
                $multiplier   = $account->getMultiplier();
                $milesEarned  = (int) round($request->amount * $multiplier);

                // Actualizar balance y lifetime_miles
                $account->increment('balance', $milesEarned);
                $account->increment('lifetime_miles', $milesEarned);

                // Verificar si sube de tier
                $newTier = Tier::getForMiles($account->fresh()->lifetime_miles);
                if ($newTier->id !== $account->tier_id) {
                    $account->update(['tier_id' => $newTier->id]);
                }

                // Registrar transacción
                Transaction::create([
                    'user_id'     => $client->id,
                    'type'        => 'earn',
                    'amount'      => $milesEarned,
                    'description' => $request->description ?? "Millas acreditadas por {$request->user()->name}",
                ]);
            });

            $account->refresh()->load('tier');

            return response()->json([
                'message' => 'Millas acreditadas correctamente',
                'data'    => [
                    'balance'        => $account->balance,
                    'lifetime_miles' => $account->lifetime_miles,
                    'multiplier'     => $account->tier->multiplier,
                    'tier'           => $account->tier->name,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al acreditar millas.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // POST /miles/redeem — Redimir millas (solo client)
    public function redeem(Request $request)
    {
        $request->validate([
            'amount'      => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $account = $request->user()->milesAccount;

        // Validar saldo suficiente
        if (! $account->hasSufficientBalance($request->amount)) {
            return response()->json([
                'message' => 'Saldo insuficiente.',
                'data'    => [
                    'balance'    => $account->balance,
                    'requested'  => $request->amount,
                    'missing'    => $request->amount - $account->balance,
                ],
            ], 422);
        }

        try {
            DB::transaction(function () use ($account, $request) {
                // Restar del balance (lifetime_miles NO baja)
                $account->decrement('balance', $request->amount);

                // Registrar transacción
                Transaction::create([
                    'user_id'     => $request->user()->id,
                    'type'        => 'redeem',
                    'amount'      => $request->amount,
                    'description' => $request->description ?? 'Redención de millas',
                ]);
            });

            $account->refresh();

            return response()->json([
                'message' => 'Millas redimidas correctamente',
                'data'    => [
                    'redeemed'       => $request->amount,
                    'balance'        => $account->balance,
                    'lifetime_miles' => $account->lifetime_miles,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al redimir millas.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // GET /miles/transactions — Historial del usuario
    public function transactions(Request $request)
    {
        $query = $request->user()->transactions()->latest();

        // Filtro por tipo
        if ($request->has('type') && in_array($request->type, ['earn', 'redeem', 'purchase'])) {
            $query->where('type', $request->type);
        }

        $transactions = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'message'      => 'Historial obtenido correctamente',
            'data'         => $transactions->items(),
            'total'        => $transactions->total(),
            'current_page' => $transactions->currentPage(),
            'last_page'    => $transactions->lastPage(),
        ]);
    }

    // GET /tiers — Listar todos los tiers
    public function tiers()
    {
        $tiers = Tier::orderBy('min_miles')->get();

        return response()->json([
            'message' => 'Tiers obtenidos correctamente',
            'data'    => $tiers,
        ]);
    }

    // Helper — obtener el siguiente tier
    private function getNextTier(int $lifetimeMiles): ?array
    {
        $next = Tier::where('min_miles', '>', $lifetimeMiles)
            ->orderBy('min_miles')
            ->first();

        if (! $next) return null;

        return [
            'name'       => $next->name,
            'min_miles'  => $next->min_miles,
            'missing'    => $next->min_miles - $lifetimeMiles,
            'multiplier' => $next->multiplier,
        ];
    }
}
