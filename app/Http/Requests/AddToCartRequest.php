<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:99',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'El producto es obligatorio.',
            'product_id.exists'   => 'El producto no existe.',
            'quantity.required'   => 'La cantidad es obligatoria.',
            'quantity.min'        => 'La cantidad mínima es 1.',
            'quantity.max'        => 'La cantidad máxima es 99.',
        ];
    }
}
