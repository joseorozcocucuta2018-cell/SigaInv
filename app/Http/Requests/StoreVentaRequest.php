<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Datos generales
            'numero' => ['required', 'string', 'max:20', 'unique:ventas'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'bodega_id' => ['required', 'exists:bodegas,id'],
            'usuario_id' => ['nullable', 'exists:users,id'],
            'cotizacion_id' => ['nullable', 'exists:cotizaciones,id'],
            'remision_id' => ['nullable', 'exists:remisiones,id'],
            'fecha' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:1000'],

            // Detalles de productos
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required', 'numeric', 'min:0.001'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'detalles.*.descuento_unitario' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.lote' => ['nullable', 'string', 'max:50'],
            'detalles.*.serial' => ['nullable', 'string', 'max:100'],
            'detalles.*.fecha_vencimiento' => ['nullable', 'date'],
            'detalles.*.impuesto_id' => ['nullable', 'exists:impuestos,id'],

            // Totales
            'subtotal' => ['required', 'numeric', 'min:0'],
            'descuento' => ['required', 'numeric', 'min:0'],
            'impuestos' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero.required' => 'El número de venta es obligatorio.',
            'numero.unique' => 'Ya existe una venta con este número.',
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'bodega_id.required' => 'Debe seleccionar una bodega de salida.',
            'bodega_id.exists' => 'La bodega seleccionada no existe.',
            'fecha.required' => 'La fecha de la venta es obligatoria.',
            'fecha.date' => 'Debe ingresar una fecha válida.',
            'detalles.required' => 'Debe agregar al menos un detalle a la venta.',
            'detalles.min' => 'La venta debe tener al menos un producto.',
            'detalles.*.producto_id.required' => 'Debe seleccionar un producto en cada línea.',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria en cada línea.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'detalles.*.precio_unitario.required' => 'El precio unitario es obligatorio.',
            'detalles.*.precio_unitario.min' => 'El precio unitario no puede ser negativo.',
        ];
    }
}
