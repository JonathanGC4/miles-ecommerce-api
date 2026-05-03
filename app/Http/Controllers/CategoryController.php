<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // GET /categories — Listar todas (público)
    public function index()
    {
        $categories = Category::where('active', true)
            ->withCount(['products' => function ($q) {
                $q->where('active', true)->where('stock', '>', 0);
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'Categorías obtenidas correctamente',
            'data'    => $categories,
        ]);
    }

    // POST /categories — Crear (admin)
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:categories',
            'icon'        => 'nullable|string|max:10',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($request->only('name', 'icon', 'description'));

        return response()->json([
            'message' => 'Categoría creada correctamente',
            'data'    => $category,
        ], 201);
    }

    // PUT /categories/{id} — Actualizar (admin)
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'        => 'sometimes|string|max:255|unique:categories,name,' . $category->id,
            'icon'        => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'active'      => 'sometimes|boolean',
        ]);

        $category->update($request->only('name', 'icon', 'description', 'active'));

        return response()->json([
            'message' => 'Categoría actualizada correctamente',
            'data'    => $category,
        ]);
    }

    // DELETE /categories/{id} — Eliminar (admin)
    public function destroy(Category $category)
    {
        // Desasignar productos de esta categoría
        $category->products()->update(['category_id' => null]);
        $category->delete();

        return response()->json([
            'message' => 'Categoría eliminada correctamente',
        ]);
    }
}
