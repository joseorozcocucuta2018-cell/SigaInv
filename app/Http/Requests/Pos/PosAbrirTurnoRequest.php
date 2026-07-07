<?php

declare(strict_types=1);

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class PosAbrirTurnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'caja_id' => ['required', 'integer', 'exists:cajas,id'],
            'bodega_id' => ['nullable', 'integer', 'exists:bodegas,id'],
            'saldo_inicial' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'caja_id.required' => 'La caja es requerida.',
            'caja_id.exists' => 'La caja seleccionada no existe.',
            'saldo_inicial.required' => 'El saldo inicial es requerido.',
            'saldo_inicial.min' => 'El saldo inicial no puede ser negativo.',
        ];
    }
}
