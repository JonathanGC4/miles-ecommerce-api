<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::available()->with('category');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->has('category')) {
            $query->whereHas('category', fn($q) =>
                $q->where('slug', $request->category)
            );
        }
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->has('sort')) {
            match($request->sort) {
                'price_asc'  => $query->orderBy('price', 'asc'),
                'price_desc' => $query->orderBy('price', 'desc'),
                'miles_desc' => $query->orderBy('miles_per_dollar', 'desc'),
                'newest'     => $query->latest(),
                default      => $query->orderBy('name'),
            };
        } else {
            $query->orderBy('name');
        }

        $products = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'message'      => 'Productos obtenidos correctamente',
            'data'         => $products->items(),
            'total'        => $products->total(),
            'current_page' => $products->currentPage(),
            'last_page'    => $products->lastPage(),
        ]);
    }

    public function show(Product $product)
    {
        return response()->json([
            'message' => 'Producto obtenido correctamente',
            'data'    => $product->load('category'),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $uploaded        = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder'         => 'miles-ecommerce/products',
                'transformation' => [['width' => 800, 'height' => 800, 'crop' => 'limit']],
            ]);
            $data['image']           = $uploaded->getPublicId();
            $data['image_public_id'] = $uploaded->getPublicId();
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

        if ($request->hasFile('image')) {
            // Eliminar imagen anterior de Cloudinary
            if ($product->image_public_id) {
                Cloudinary::destroy($product->image_public_id);
            }

            $uploaded                = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder'         => 'miles-ecommerce/products',
                'transformation' => [['width' => 800, 'height' => 800, 'crop' => 'limit']],
            ]);
            $data['image']           = $uploaded->getSecurePath();
            $data['image_public_id'] = $uploaded->getPublicId();
        }

        $product->update($data);

        return response()->json([
            'message' => 'Producto actualizado correctamente',
            'data'    => $product,
        ]);
    }

    public function destroy(Product $product)
    {
        if ($product->image_public_id) {
            Cloudinary::destroy($product->image_public_id);
        }
        $product->update(['active' => false]);

        return response()->json([
            'message' => 'Producto desactivado correctamente',
        ]);
    }

    public function adminIndex(Request $request)
    {
        $products = Product::with('category')
            ->when($request->has('search'), fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
            )
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
