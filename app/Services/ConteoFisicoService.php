<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AjusteEstado;
use App\Enums\ConteoFisicoEstado;
use App\Enums\MotivoAjuste;
use App\Models\AjusteInventario;
use App\Models\ConteoFisico;
use App\Models\DetalleAjusteInventario;
use App\Models\DetalleConteoFisico;
use App\Models\Producto;
use App\Models\StockBodega;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConteoFisicoService
{
    /**
     * Genera un conteo físico.
     * Si es Saldo Inicial o la bodega está vacía, carga todo el catálogo activo.
     */
    public function generarConteo(int $bodegaId, ?string $observacion = null, bool $esSaldoInicial = false): ConteoFisico
    {
        return DB::transaction(function () use ($bodegaId, $observacion, $esSaldoInicial) {
            if ($esSaldoInicial) {
                $existeSaldoInicial = ConteoFisico::where('bodega_id', $bodegaId)->where('es_saldo_inicial', true)->exists();
                if ($existeSaldoInicial) {
                    throw new \InvalidArgumentException('Ya existe un saldo inicial para esta bodega. Solo se permite uno.');
                }
            }

            $conteo = ConteoFisico::create([
                'bodega_id' => $bodegaId,
                'usuario_id' => Auth::id(),
                'fecha_inicio' => now(),
                'estado' => ConteoFisicoEstado::ABIERTO,
                'es_saldo_inicial' => $esSaldoInicial,
                'observacion' => $observacion,
            ]);

            // Verificamos si hay stock en esta bodega
            $tieneStock = StockBodega::where('bodega_id', $bodegaId)->where('cantidad', '>', 0)->exists();

            if ($esSaldoInicial || ! $tieneStock) {
                // Carga exhaustiva: todos los productos activos
                $productos = Producto::where('activo', true)->get();
                foreach ($productos as $producto) {
                    $stock = StockBodega::where('bodega_id', $bodegaId)
                        ->where('producto_id', $producto->id)
                        ->first();

                    DetalleConteoFisico::create([
                        'conteo_fisico_id' => $conteo->id,
                        'producto_id' => $producto->id,
                        'stock_sistema' => $stock?->cantidad ?? 0,
                        'cantidad_contada' => null,
                        'diferencia' => 0,
                    ]);
                }
            } else {
                // Carga estándar: solo lo que tiene stock
                $stocks = StockBodega::where('bodega_id', $bodegaId)
                    ->where('cantidad', '>', 0)
                    ->get();

                foreach ($stocks as $stock) {
                    DetalleConteoFisico::create([
                        'conteo_fisico_id' => $conteo->id,
                        'producto_id' => $stock->producto_id,
                        'stock_sistema' => $stock->cantidad,
                        'cantidad_contada' => null,
                        'diferencia' => 0,
                    ]);
                }
            }

            return $conteo;
        });
    }

    /**
     * Cierra el conteo: calcula diferencias entre lo contado y el sistema.
     */
    public function cerrarConteo(ConteoFisico $conteo): void
    {
        if ($conteo->estado !== ConteoFisicoEstado::ABIERTO) {
            throw new \InvalidArgumentException('Solo se pueden cerrar conteos en estado abierto.');
        }

        $detalles = $conteo->detalles()->whereNotNull('cantidad_contada')->get();

        if ($detalles->isEmpty()) {
            throw new \InvalidArgumentException('No hay líneas con cantidad contada registrada.');
        }

        DB::transaction(function () use ($conteo, $detalles) {
            foreach ($detalles as $detalle) {
                $detalle->update([
                    'diferencia' => $detalle->cantidad_contada - $detalle->stock_sistema,
                ]);
            }

            $conteo->update([
                'estado' => ConteoFisicoEstado::CERRADO,
                'fecha_cierre' => now(),
            ]);
        });
    }

    /**
     * Genera un AjusteInventario automático a partir de las diferencias del conteo.
     */
    public function generarAjuste(ConteoFisico $conteo): ?AjusteInventario
    {
        if ($conteo->estado !== ConteoFisicoEstado::CERRADO) {
            throw new \InvalidArgumentException('Solo se pueden generar ajustes de conteos cerrados.');
        }

        $detallesConDiferencia = $conteo->detalles()
            ->where('diferencia', '!=', 0)
            ->whereNotNull('cantidad_contada')
            ->get();

        if ($detallesConDiferencia->isEmpty()) {
            throw new \InvalidArgumentException('No hay diferencias que ajustar.');
        }

        return DB::transaction(function () use ($conteo, $detallesConDiferencia) {
            $ajuste = AjusteInventario::create([
                'bodega_id' => $conteo->bodega_id,
                'usuario_id' => Auth::id() ?? $conteo->usuario_id,
                'fecha' => now(),
                'motivo' => $conteo->es_saldo_inicial ? MotivoAjuste::AJUSTE_INICIAL : MotivoAjuste::CONTEO_FISICO,
                'estado' => AjusteEstado::BORRADOR,
                'observacion' => $conteo->es_saldo_inicial
                    ? "Saldo inicial generado desde conteo #{$conteo->numero}"
                    : "Generado desde conteo #{$conteo->numero}",
            ]);

            foreach ($detallesConDiferencia as $detalle) {
                $producto = $detalle->producto;
                $costoUnitario = $producto?->costo_promedio ?? $producto?->precio_compra ?? 0;

                DetalleAjusteInventario::create([
                    'ajuste_inventario_id' => $ajuste->id,
                    'producto_id' => $detalle->producto_id,
                    'stock_sistema' => $detalle->stock_sistema,
                    'stock_fisico' => $detalle->cantidad_contada,
                    'diferencia' => $detalle->diferencia,
                    'costo_unitario' => $costoUnitario,
                ]);

                $detalle->update([
                    'ajuste_inventario_id' => $ajuste->id,
                ]);
            }

            $conteo->update([
                'estado' => ConteoFisicoEstado::AJUSTADO,
            ]);

            return $ajuste;
        });
    }

    /**
     * Procesa el conteo completo en un solo paso (Cierre + Ajuste + Confirmación).
     */
    public function procesarConteoDirecto(ConteoFisico $conteo): void
    {
        DB::transaction(function () use ($conteo) {
            $this->cerrarConteo($conteo);
            $ajuste = $this->generarAjuste($conteo);
            if ($ajuste) {
                app(AjusteInventarioService::class)->confirmar($ajuste);
            }
        });
    }

    /**
     * Agrega un producto manualmente al conteo.
     */
    public function agregarProductoAConteo(ConteoFisico $conteo, int $productoId): DetalleConteoFisico
    {
        if ($conteo->estado !== ConteoFisicoEstado::ABIERTO) {
            throw new \InvalidArgumentException('Solo se pueden agregar productos a conteos en estado abierto.');
        }

        $existente = DetalleConteoFisico::where('conteo_fisico_id', $conteo->id)
            ->where('producto_id', $productoId)
            ->first();

        if ($existente) {
            return $existente;
        }

        $stock = StockBodega::where('bodega_id', $conteo->bodega_id)
            ->where('producto_id', $productoId)
            ->first();

        return DetalleConteoFisico::create([
            'conteo_fisico_id' => $conteo->id,
            'producto_id' => $productoId,
            'stock_sistema' => $stock?->cantidad ?? 0,
            'cantidad_contada' => null,
            'diferencia' => 0,
        ]);
    }

    /**
     * Sincroniza el conteo con el catálogo de productos activos.
     */
    public function sincronizarProductosCatalogo(ConteoFisico $conteo): void
    {
        if ($conteo->estado !== ConteoFisicoEstado::ABIERTO) {
            return;
        }

        DB::transaction(function () use ($conteo) {
            $productosFaltantes = Producto::where('activo', true)
                ->whereNotExists(function ($query) use ($conteo) {
                    $query->select(DB::raw(1))
                        ->from('detalle_conteos_fisicos')
                        ->whereRaw('detalle_conteos_fisicos.producto_id = productos.id')
                        ->where('detalle_conteos_fisicos.conteo_fisico_id', $conteo->id);
                })
                ->get();

            foreach ($productosFaltantes as $producto) {
                $this->agregarProductoAConteo($conteo, $producto->id);
            }
        });
    }
}
