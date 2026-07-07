<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'codigo' => (string) $this->codigo,
            'codigo_barras' => $this->codigo_barras,
            'nombre' => (string) $this->nombre,
            'nombre_comun' => $this->nombre_comun,
            'descripcion' => $this->descripcion,
            'precio_venta' => (float) $this->precio_venta,
            'stock_minimo' => (float) $this->stock_minimo,
            'stock_maximo' => $this->stock_maximo ? (float) $this->stock_maximo : null,
            'imagen' => $this->imagen,
            'imagen_url' => $this->imagen ? asset('storage/'.$this->imagen) : null,
            'activo' => (bool) $this->activo,
            'exige_lote' => (bool) $this->exige_lote,
            'exige_serial' => (bool) $this->exige_serial,
            'impuesto' => $this->whenLoaded('impuesto', fn () => [
                'id' => (int) $this->impuesto->id,
                'nombre' => (string) $this->impuesto->nombre,
                'porcentaje' => (float) $this->impuesto->porcentaje,
            ]),
            'unidad_medida' => $this->whenLoaded('unidadMedida', fn () => [
                'id' => (int) $this->unidadMedida->id,
                'nombre' => (string) $this->unidadMedida->nombre,
            ]),
        ];
    }
}
