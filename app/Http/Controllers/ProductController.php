<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
public function index(Request $request)
{
    $query = Product::available()->with('category');

    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    if ($request->filled('category')) {
        $query->whereHas('category', function ($q) use ($request) {
            $q->where('slug', $request->category);
        });
    }

    if ($request->filled('min_price')) {
        $query->where('price', '>=', (float) $request->min_price);
    }

    if ($request->filled('max_price')) {
        $query->where('price', '<=', (float) $request->max_price);
    }

    if ($request->filled('min_miles')) {
        $query->where('miles_per_dollar', '>=', (int) $request->min_miles);
    }

    switch ($request->sort) {
        case 'price_asc':
            $query->orderBy('price', 'asc');
            break;
        case 'price_desc':
            $query->orderBy('price', 'desc');
            break;
        case 'miles_desc':
            $query->orderBy('miles_per_dollar', 'desc');
            break;
        case 'newest':
            $query->latest();
            break;
        default:
            $query->orderBy('name');
    }

    $products = $query->paginate($request->get('per_page', 12));

    return response()->json([
        'message' => 'Productos obtenidos correctamente',
        'data' => $products->items(),
        'total' => $products->total(),
        'current_page' => $products->currentPage(),
        'last_page' => $products->lastPage(),
    ]);
}

    public function show(Product $product)
    {
        return response()->json([
            'message' => 'Producto obtenido correctamente',
            'data'    => $product,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        // Subir imagen si viene
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('products', 'public');
        }

        $product = Product::create($data);

        return response()->json([
            'message' => 'Producto creado correctamente',
            'data'    => $product,
        ], 201);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        // Subir nueva imagen y eliminar la anterior
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')
                ->store('products', 'public');
        }

        $product->update($data);

        return response()->json([
            'message' => 'Producto actualizado correctamente',
            'data'    => $product,
        ]);
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->update(['active' => false]);

        return response()->json([
            'message' => 'Producto desactivado correctamente',
        ]);
    }

    public function adminIndex(Request $request)
    {
        $products = Product::when($request->has('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('name')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'message'      => 'Productos obtenidos correctamente',
            'data'         => $products->items(),
            'total'        => $products->total(),
            'current_page' => $products->currentPage(),
            'last_page'    => $products->lastPage(),
        ]);
    }
}
