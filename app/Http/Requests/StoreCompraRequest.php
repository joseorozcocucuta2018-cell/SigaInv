<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Datos generales
            'numero' => ['required', 'string', 'max:20', 'unique:compras'],
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'bodega_id' => ['required', 'exists:bodegas,id'],
            'usuario_id' => ['nullable', 'exists:users,id'],
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

            // Totales (recalculados, pero validamos que sea consistente)
            'subtotal' => ['required', 'numeric', 'min:0'],
            'descuento' => ['required', 'numeric', 'min:0'],
            'impuestos' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero.required' => 'El número de factura es obligatorio.',
            'numero.unique' => 'Ya existe una compra con este número de factura.',
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            'bodega_id.required' => 'Debe seleccionar una bodega de entrada.',
            'bodega_id.exists' => 'La bodega seleccionada no existe.',
            'fecha.required' => 'La fecha de la compra es obligatoria.',
            'fecha.date' => 'Debe ingresar una fecha válida.',
            'detalles.required' => 'Debe agregar al menos un detalle a la compra.',
            'detalles.min' => 'La compra debe tener al menos un producto.',
            'detalles.*.producto_id.required' => 'Debe seleccionar un producto en cada línea.',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria en cada línea.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'detalles.*.precio_unitario.required' => 'El precio unitario es obligatorio.',
            'detalles.*.precio_unitario.min' => 'El precio unitario no puede ser negativo.',
        ];
    }
}
