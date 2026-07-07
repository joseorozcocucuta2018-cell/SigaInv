<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TurnoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'usuario_id' => (int) $this->usuario_id,
            'caja_id' => (int) $this->caja_id,
            'bodega_id' => $this->bodega_id ? (int) $this->bodega_id : null,
            'estado' => is_object($this->estado) ? $this->estado->value : (string) $this->estado,
            'saldo_inicial' => (float) $this->saldo_inicial,
            'saldo_final_esperado' => $this->saldo_final_esperado ? (float) $this->saldo_final_esperado : null,
            'saldo_final_real' => $this->saldo_final_real ? (float) $this->saldo_final_real : null,
            'diferencia' => $this->diferencia ? (float) $this->diferencia : null,
            'fecha_apertura' => optional($this->fecha_apertura)->toIso8601String(),
            'fecha_cierre' => optional($this->fecha_cierre)->toIso8601String(),
            'observaciones' => $this->observaciones,
        ];
    }
}
