<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RemisionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'numero' => (string) $this->numero,
            'estado' => is_object($this->estado) ? $this->estado->value : (string) $this->estado,
            'estado_pago' => is_object($this->estado_pago) ? $this->estado_pago->value : (string) $this->estado_pago,
            'cliente_id' => (int) $this->cliente_id,
            'bodega_id' => (int) $this->bodega_id,
            'fecha' => optional($this->fecha)->toIso8601String(),
            'subtotal' => (float) $this->subtotal,
            'descuento' => (float) $this->descuento,
            'impuestos' => (float) $this->impuestos,
            'total' => (float) $this->total,
            'saldo_pendiente' => (float) $this->saldo_pendiente,
            'observaciones' => $this->observaciones,
        ];
    }
}
