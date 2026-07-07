<?php

declare(strict_types=1);

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class PosFinalizarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', 'in:venta,remision,cotizacion'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'El tipo de documento es requerido.',
            'tipo.in' => 'El tipo de documento debe ser: venta, remisión o cotización.',
        ];
    }
}
