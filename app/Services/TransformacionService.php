<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransformacionEstado;
use App\Models\Empresa;
use App\Models\FormulaTransformacion;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\Transformacion;
use App\Models\TransformacionDetalle;
use Exception;
use Illuminate\Support\Facades\DB;

class TransformacionService
{
    public function __construct(
        private readonly CostoPromedioService $costoPromedioService
    ) {}

    /**
     * Confirma una transformación en borrador:
     * - Valida stock de insumos
     * - Descuenta insumos del stock
     * - Suma el producto final al stock y actualiza CPP
     * - Si no había fórmula vinculada, la crea automáticamente
     * - Actualiza estado → confirmada
     *
     * @throws Exception
     */
    public function confirmar(Transformacion $transformacion): void
    {
        $transformacion->validarConfirmable();

        DB::transaction(function () use ($transformacion) {
            $insumos = $transformacion->insumos()->with('producto')->get();
            $bodegaId = $transformacion->bodega_id;
            $cantidad = (float) $transformacion->cantidad_a_producir;

            // 1. Validar stock suficiente para todos los insumos
            foreach ($insumos as $insumo) {
                $stock = StockBodega::where('bodega_id', $bodegaId)
                    ->where('producto_id', $insumo->producto_id)
                    ->lockForUpdate()
                    ->first();

                $disponible = (float) ($stock?->cantidad ?? 0);

                if ($disponible < (float) $insumo->cantidad) {
                    throw new Exception(
                        "Stock insuficiente para '{$insumo->producto?->nombre}'. ".
                        "Requiere: {$insumo->cantidad}, Disponible: {$disponible}"
                    );
                }
            }

            // 2. Descontar insumos y registrar movimientos de salida
            $costoTotalInsumos = 0.0;

            foreach ($insumos as $insumo) {
                $stock = StockBodega::where('bodega_id', $bodegaId)
                    ->where('producto_id', $insumo->producto_id)
                    ->lockForUpdate()
                    ->first();

                $stock->decrement('cantidad', $insumo->cantidad);
                $costoTotalInsumos += (float) $insumo->cantidad * (float) ($insumo->costo_unitario ?? 0);

                MovimientoInventario::create([
                    'producto_id' => $insumo->producto_id,
                    'bodega_id' => $bodegaId,
                    'tipo_movimiento' => 'salida_transformacion',
                    'cantidad' => $insumo->cantidad,
                    'costo_unitario' => $insumo->costo_unitario,
                    'lote' => $insumo->lote,
                    'stock_resultante' => $stock->cantidad,
                    'documento_tipo' => 'transformacion',
                    'documento_id' => $transformacion->id,
                    'usuario_id' => $transformacion->usuario_id,
                ]);
            }

            // 3. Sumar producto final al stock y actualizar CPP
            $costoUnitarioFinal = $cantidad > 0 ? $costoTotalInsumos / $cantidad : 0;

            $stockFinal = StockBodega::where('bodega_id', $bodegaId)
                ->where('producto_id', $transformacion->producto_final_id)
                ->lockForUpdate()
                ->first();

            if (! $stockFinal) {
                $stockFinal = StockBodega::create([
                    'bodega_id' => $bodegaId,
                    'producto_id' => $transformacion->producto_final_id,
                    'cantidad' => 0,
                ]);
            }

            $stockAntes = (float) $stockFinal->cantidad;
            $stockFinal->increment('cantidad', $cantidad);

            $this->costoPromedioService->calcularCostoPromedio(
                $transformacion->producto_final_id,
                $stockAntes,
                $cantidad,
                $costoUnitarioFinal
            );

            MovimientoInventario::create([
                'producto_id' => $transformacion->producto_final_id,
                'bodega_id' => $bodegaId,
                'tipo_movimiento' => 'entrada_transformacion',
                'cantidad' => $cantidad,
                'costo_unitario' => $costoUnitarioFinal,
                'stock_resultante' => $stockAntes + $cantidad,
                'documento_tipo' => 'transformacion',
                'documento_id' => $transformacion->id,
                'usuario_id' => $transformacion->usuario_id,
            ]);

            // 3.1 Registrar el detalle del producto final
            $transformacion->detalles()->create([
                'tipo_linea' => 'producto',
                'producto_id' => $transformacion->producto_final_id,
                'cantidad' => $cantidad,
                'costo_unitario' => $costoUnitarioFinal,
            ]);

            // 4. Si no existe fórmula vinculada, crearla automáticamente
            $formulaId = $transformacion->formula_transformacion_id;

            if (! $formulaId) {
                $nombreProducto = $transformacion->productoFinal?->nombre
                    ?? 'Producto #'.$transformacion->producto_final_id;

                $formula = FormulaTransformacion::create([
                    'producto_final_nombre' => 'AUTO: '.$nombreProducto,
                    'tipo' => $transformacion->tipo?->value ?? 'fabricacion',
                    'producto_final_id' => $transformacion->producto_final_id,
                    'cantidad_producto_final' => $cantidad,
                    'activo' => true,
                    'usuario_id' => $transformacion->usuario_id,
                ]);

                // Normalizar cantidades de insumos a 1 unidad de producto final
                foreach ($insumos as $insumo) {
                    $formula->detalles()->create([
                        'tipo_linea' => 'insumo',
                        'producto_id' => $insumo->producto_id,
                        'cantidad' => $cantidad > 0 ? (float) $insumo->cantidad / $cantidad : (float) $insumo->cantidad,
                        'costo_unitario' => $insumo->costo_unitario,
                    ]);
                }

                $formulaId = $formula->id;
            }

            // 5. Marcar fórmula como usada y guardar costo total
            FormulaTransformacion::where('id', $formulaId)->update(['tiene_transformaciones' => true]);

            $transformacion->update([
                'costo_total' => $costoTotalInsumos,
            ]);

            // 5.1 Solo actualizar precio de venta si es la primera producción (sin stock previo)
            if ($stockAntes == 0 && $transformacion->producto_final_id) {
                $productoFinal = Producto::find($transformacion->producto_final_id);
                if ($productoFinal) {
                    $nuevoPrecio = null;

                    if ($transformacion->tipo_calculo_precio?->value === 'manual') {
                        $nuevoPrecio = $transformacion->precio_sugerido;
                    } elseif ($transformacion->tipo_calculo_precio?->value === 'margen') {
                        $margen = $transformacion->margen_deseado
                            ?? Empresa::actual()?->margen_ganancia_default
                            ?? 0;

                        if ($costoUnitarioFinal > 0 && $margen < 100) {
                            $nuevoPrecio = $costoUnitarioFinal / (1 - ($margen / 100));
                        }
                    }

                    if ($nuevoPrecio !== null) {
                        $productoFinal->update([
                            'precio_venta' => round((float) $nuevoPrecio, 2),
                        ]);
                    }
                }
            }

            // 6. Actualizar estado de la transformación
            $transformacion->update([
                'estado' => TransformacionEstado::CONFIRMADA,
                'confirmada_en' => now(),
                'formula_transformacion_id' => $formulaId,
            ]);
        });
    }

    /**
     * Revierte una transformación confirmada (solo Combo y Promo):
     * - Devuelve insumos al stock
     * - Descuenta el producto final del stock
     * - Actualiza estado → revertida
     *
     * @throws Exception
     */
    public function revertir(Transformacion $transformacion): void
    {
        if (! $transformacion->estado->canRevert()) {
            throw new Exception(
                'Solo se puede revertir una transformación en estado Confirmada.'
            );
        }

        if (! $transformacion->esReversible()) {
            throw new Exception(
                'Solo se pueden revertir transformaciones de tipo Combo o Promoción.'
            );
        }

        DB::transaction(function () use ($transformacion) {
            $insumos = $transformacion->insumos()->with('producto')->get();
            $bodegaId = $transformacion->bodega_id;
            $cantidad = (float) $transformacion->cantidad_a_producir;

            // 1. Validar que haya stock del producto final para devolver
            $stockFinal = StockBodega::where('bodega_id', $bodegaId)
                ->where('producto_id', $transformacion->producto_final_id)
                ->lockForUpdate()
                ->first();

            if (! $stockFinal || (float) $stockFinal->cantidad < $cantidad) {
                $disponible = (float) ($stockFinal?->cantidad ?? 0);
                throw new Exception(
                    'Stock insuficiente del producto final para revertir. '.
                    "Requiere: {$cantidad}, Disponible: {$disponible}"
                );
            }

            // 2. Descontar producto final del stock
            $stockFinal->decrement('cantidad', $cantidad);

            MovimientoInventario::create([
                'producto_id' => $transformacion->producto_final_id,
                'bodega_id' => $bodegaId,
                'tipo_movimiento' => 'reverso_transformacion',
                'cantidad' => $cantidad,
                'costo_unitario' => 0,
                'stock_resultante' => $stockFinal->cantidad,
                'documento_tipo' => 'transformacion',
                'documento_id' => $transformacion->id,
                'usuario_id' => $transformacion->usuario_id,
            ]);

            // 3. Devolver insumos al stock (con lock para evitar race conditions)
            foreach ($insumos as $insumo) {
                $stock = StockBodega::where('bodega_id', $bodegaId)
                    ->where('producto_id', $insumo->producto_id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    $stock = StockBodega::create([
                        'bodega_id' => $bodegaId,
                        'producto_id' => $insumo->producto_id,
                        'cantidad' => 0,
                    ]);
                    $stock->refresh();
                    $stock = $stock->lockForUpdate();
                }

                $stock->increment('cantidad', $insumo->cantidad);
                $stock->refresh();

                MovimientoInventario::create([
                    'producto_id' => $insumo->producto_id,
                    'bodega_id' => $bodegaId,
                    'tipo_movimiento' => 'reverso_transformacion',
                    'cantidad' => $insumo->cantidad,
                    'costo_unitario' => $insumo->costo_unitario,
                    'lote' => $insumo->lote,
                    'stock_resultante' => $stock->cantidad,
                    'documento_tipo' => 'transformacion',
                    'documento_id' => $transformacion->id,
                    'usuario_id' => $transformacion->usuario_id,
                ]);
            }

            // 4. Si no quedan transformaciones activas con esta fórmula, liberar
            if ($transformacion->formula_transformacion_id) {
                $activas = Transformacion::where('formula_transformacion_id', $transformacion->formula_transformacion_id)
                    ->where('estado', TransformacionEstado::CONFIRMADA)
                    ->where('id', '!=', $transformacion->id)
                    ->exists();

                if (! $activas) {
                    FormulaTransformacion::where('id', $transformacion->formula_transformacion_id)
                        ->update(['tiene_transformaciones' => false]);
                }
            }

            // 5. Actualizar estado
            $transformacion->update([
                'estado' => TransformacionEstado::REVERTIDA,
                'revertida_en' => now(),
            ]);
        });
    }

    /**
     * Aplica una fórmula a una transformación existente generando sus líneas de insumos.
     * Se llama desde EditTransformacion::afterSave() cuando cambia la fórmula o la cantidad.
     *
     * @throws Exception
     */
    /**
     * Carga detalles desde la formula si la transformacion no tiene detalles propios
     * Se ejecuta al montar la pagina de edicion
     */
    public function cargarDetallesDesdeFormula(Transformacion $transformacion): void
    {
        if ($transformacion->detalles()->count() > 0 || ! $transformacion->formula_transformacion_id) {
            return;
        }

        $formula = $transformacion->formula;
        if (! $formula) {
            return;
        }

        $cantidadAProducir = (float) ($transformacion->cantidad_a_producir ?? 1);

        foreach ($formula->detalles as $detalle) {
            $transformacion->detalles()->create([
                'tipo_linea' => $detalle->tipo_linea,
                'producto_id' => $detalle->producto_id,
                'cantidad' => $detalle->cantidad * $cantidadAProducir,
                'costo_unitario' => CostoService::resolveCostoUnitario($detalle->producto),
            ]);
        }

        $transformacion->refresh();
    }

    /**
     * Re-aplica la formula si cambiaron campos clave (formula o cantidad)
     */
    public function reapplyFormulaIfNeeded(Transformacion $transformacion): void
    {
        if (
            ! $transformacion->formula_transformacion_id ||
            (! $transformacion->wasChanged('formula_transformacion_id') && ! $transformacion->wasChanged('cantidad_a_producir'))
        ) {
            return;
        }

        $formula = FormulaTransformacion::findOrFail($transformacion->formula_transformacion_id);
        $this->applyFormulaToTransformacion(
            $transformacion,
            $formula,
            (float) ($transformacion->cantidad_a_producir ?? 1)
        );
    }

    /**
     * Aplica una fórmula a una transformación existente generando sus líneas de insumos.
     * Se llama desde EditTransformacion::afterSave() cuando cambia la fórmula o la cantidad.
     *
     * @throws Exception
     */
    public function applyFormulaToTransformacion(
        Transformacion $transformacion,
        FormulaTransformacion $formula,
        float $cantidadAProducir
    ): void {
        DB::transaction(function () use ($transformacion, $formula, $cantidadAProducir) {
            TransformacionDetalle::where('transformacion_id', $transformacion->id)->forceDelete();

            $transformacion->update([
                'formula_transformacion_id' => $formula->id,
                'cantidad_a_producir' => $cantidadAProducir,
                'producto_final_id' => $formula->producto_final_id ?? $transformacion->producto_final_id,
            ]);

            foreach ($formula->detalles()->where('tipo_linea', 'insumo')->get() as $detalle) {
                TransformacionDetalle::create([
                    'transformacion_id' => $transformacion->id,
                    'tipo_linea' => 'insumo',
                    'producto_id' => $detalle->producto_id,
                    'cantidad' => $detalle->cantidad * $cantidadAProducir,
                    'lote' => null,
                    'fecha_vencimiento' => null,
                    'costo_unitario' => $detalle->producto?->precio_compra ?? 0,
                ]);
            }
        });
    }

    /**
     * Valida stock de insumos de una fórmula antes de aplicarla.
     *
     * @throws Exception
     */
    public function validateInsumsAvailability(
        FormulaTransformacion $formula,
        int $bodegaId,
        float $cantidadAProducir
    ): void {
        $insumos = $formula->detalles()->where('tipo_linea', 'insumo')->get();

        foreach ($insumos as $insumo) {
            $cantidadRequerida = $insumo->cantidad * $cantidadAProducir;

            $stock = StockBodega::where('bodega_id', $bodegaId)
                ->where('producto_id', $insumo->producto_id)
                ->first();

            $disponible = (float) ($stock?->cantidad ?? 0);

            if ($disponible < $cantidadRequerida) {
                throw new Exception(
                    "Stock insuficiente para '{$insumo->producto?->nombre}'. ".
                    "Requiere: {$cantidadRequerida}, Disponible: {$disponible}"
                );
            }
        }
    }
}
