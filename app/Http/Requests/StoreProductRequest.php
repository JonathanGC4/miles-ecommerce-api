<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

public function rules(): array
{
    return [
        'name'             => 'required|string|max:255',
        'description'      => 'nullable|string',
        'price'            => 'required|numeric|min:0.01',
        'stock'            => 'required|integer|min:0',
        'miles_per_dollar' => 'required|integer|min:1',
        'active'           => 'boolean',
        'image'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'category_id' => 'nullable|exists:categories,id',
    ];
}

    public function messages(): array
    {
        return [
            'name.required'             => 'El nombre del producto es obligatorio.',
            'price.required'            => 'El precio es obligatorio.',
            'price.min'                 => 'El precio debe ser mayor a 0.',
            'stock.required'            => 'El stock es obligatorio.',
            'miles_per_dollar.required' => 'Las millas por dólar son obligatorias.',
            'miles_per_dollar.min'      => 'Las millas por dólar deben ser al menos 1.',
        ];
    }
}
