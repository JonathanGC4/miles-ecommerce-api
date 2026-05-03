<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

public function rules(): array
{
    return [
        'name'             => 'sometimes|string|max:255',
        'description'      => 'nullable|string',
        'price'            => 'sometimes|numeric|min:0.01',
        'stock'            => 'sometimes|integer|min:0',
        'miles_per_dollar' => 'sometimes|integer|min:1',
        'active'           => 'sometimes|boolean',
        'image'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'category_id' => 'nullable|exists:categories,id',
    ];
}
}
