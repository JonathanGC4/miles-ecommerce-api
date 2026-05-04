<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Listar todos los employees
    public function index()
    {
        $users = User::where('role', 'employee')
                     ->withCount('tasks')
                     ->latest()
                     ->get();

        return response()->json([
            'message' => 'Usuarios obtenidos correctamente',
            'data'    => $users,
        ]);
    }

    // Crear employee
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'employee',
        ]);

        return response()->json([
            'message' => 'Employee creado correctamente',
            'data'    => $user,
        ], 201);
    }

    // Eliminar employee
    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'No puedes eliminar a un administrador.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'Employee eliminado correctamente',
        ]);
    }
    // Buscar cliente por email
public function findByEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $client = User::where('email', $request->email)
                  ->where('role', 'client')
                  ->with('milesAccount.tier')
                  ->first();

    if (! $client) {
        return response()->json([
            'message' => 'Cliente no encontrado.',
        ], 404);
    }

    return response()->json([
        'message' => 'Cliente encontrado',
        'data'    => $client,
    ]);
}
// Listar todos los clients para el select
public function clients()
{
    $clients = User::where('role', 'client')
        ->with('milesAccount.tier')
        ->orderBy('name')
        ->get()
        ->map(fn($u) => [
            'id'           => $u->id,
            'name'         => $u->name,
            'email'        => $u->email,
            'tier'         => $u->milesAccount?->tier?->name,
            'balance'      => $u->milesAccount?->balance,
            'lifetime_miles' => $u->milesAccount?->lifetime_miles,
            'multiplier'   => $u->milesAccount?->tier?->multiplier,
        ]);

    return response()->json([
        'message' => 'Clientes obtenidos correctamente',
        'data'    => $clients,
    ]);
}
}
