<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EarnMilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'     => 'required|exists:users,id',
            'amount'      => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Debes especificar el cliente.',
            'user_id.exists'   => 'El cliente no existe.',
            'amount.required'  => 'El monto de millas es obligatorio.',
            'amount.min'       => 'El monto debe ser mayor a 0.',
        ];
    }
}
