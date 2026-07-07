<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Categoria;
use App\Models\Impuesto;
use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;

class ProductoService
{
    /**
     * Crea un producto nuevo aplicando reglas de negocio estándar.
     */
    public static function crear(array $data): Producto
    {
        // 1. Normalizar texto (regla de negocio DDD)
        $data['nombre'] = mb_convert_case(trim($data['nombre']), MB_CASE_TITLE, 'UTF-8');

        if (! empty($data['codigo_barras'])) {
            $data['codigo_barras'] = trim($data['codigo_barras']);

            if (Producto::where('codigo_barras', $data['codigo_barras'])->exists()) {
                throw new \InvalidArgumentException("El código de barras '{$data['codigo_barras']}' ya está registrado.");
            }
        }

        // 2. Asegurar Marca "Marca Propia" si no se proporciona
        if (empty($data['marca_id'])) {
            $marca = Marca::firstOrCreate(['nombre' => mb_strtoupper('Marca Propia')], ['activo' => true]);
            $data['marca_id'] = $marca->id;
        }

        // 3. Asegurar Impuesto por defecto
        if (empty($data['impuesto_id'])) {
            $impuesto = Impuesto::where('porcentaje', 0)->first()
                ?? Impuesto::first()
                ?? Impuesto::create(['nombre' => 'Exento', 'porcentaje' => 0]);
            $data['impuesto_id'] = $impuesto->id;
        }

        // 3b. Asegurar Categoría por defecto
        if (empty($data['categoria_id'])) {
            $categoria = Categoria::firstOrCreate(
                ['nombre' => mb_strtoupper('General')],
                ['activo' => true],
            );
            $data['categoria_id'] = $categoria->id;
        }

        // 4. Defaults
        $data['usuario_id'] = Auth::id();
        $data['activo'] = true;
        $data['tipo_producto'] = $data['tipo_producto'] ?? 'comprado';

        if (empty($data['costo_promedio'])) {
            $data['costo_promedio'] = $data['precio_compra'] ?? 0;
        }

        return Producto::create($data);
    }
}
