<?php

declare(strict_types=1);

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class PosCrearVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'bodega_id' => ['nullable', 'integer', 'exists:bodegas,id'],
            'descuento_global' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'pagos' => ['nullable', 'array'],
            'pagos.*.forma_pago_id' => ['required_with:pagos', 'integer', 'exists:formas_pago,id'],
            'pagos.*.monto' => ['required_with:pagos', 'numeric', 'min:0.01'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.descuento_unitario' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'El cliente es requerido.',
            'items.required' => 'La venta debe tener al menos un producto.',
            'items.min' => 'La venta debe tener al menos un producto.',
        ];
    }
}
