<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'nombre' => (string) $this->nombre,
            'tipo_documento' => (string) $this->tipo_documento,
            'documento' => (string) $this->documento,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'direccion1' => $this->direccion1,
            'saldo' => (float) $this->saldo,
            'limite_credito' => (float) $this->limite_credito,
            'dias_credito' => (int) $this->dias_credito,
            'porcentaje_descuento' => (float) $this->porcentaje_descuento,
            'estado' => is_object($this->estado) ? $this->estado->value : (string) $this->estado,
        ];
    }
}
