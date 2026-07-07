<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\HistoricoPrecio;
use App\Models\Producto;
use App\Traits\RegistraAuditoria;
use Illuminate\Support\Facades\Log;

class ProductoObserver
{
    use RegistraAuditoria;

    /**
     * Handle the Producto "creating" event.
     */
    public function creating(Producto $producto): void
    {
        if (empty($producto->codigo)) {
            $nextId = (Producto::max('id') ?? 0) + 1;

            $prefix = match ($producto->tipo_producto) {
                'comprado' => 'COM-',
                'manufacturado' => 'MAN-',
                'materia_prima' => 'MAT-',
                'servicio' => 'SER-',
                default => 'PROD-',
            };

            $producto->codigo = $prefix.str_pad((string) $nextId, 7, '0', STR_PAD_LEFT);
        }

        if (auth()->check() && ! $producto->usuario_id) {
            $producto->usuario_id = auth()->id();
        }
    }

    /**
     * Handle the Producto "updating" event.
     */
    public function updating(Producto $producto): void
    {
        // El código es inmutable una vez creado
        if ($producto->isDirty('codigo')) {
            $producto->codigo = $producto->getOriginal('codigo');
        }
    }

    /**
     * Handle the Producto "updated" event.
     * Registra cambios de campos en productos
     */
    public function updated(Producto $producto): void
    {
        // Registrar en historico_precios si cambia precio_compra
        if ($producto->isDirty('precio_compra')) {
            try {
                HistoricoPrecio::create([
                    'producto_id' => $producto->id,
                    'proveedor_id' => null,
                    'precio_compra' => $producto->precio_compra,
                    'usuario_id' => auth()->id(),
                    'fecha_cambio' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning("Error al registrar histórico de precio: {$e->getMessage()}");
            }
        }
        // Obtener los atributos modificados
        $dirty = $producto->getDirty();

        // Obtener los valores originales
        $original = $producto->getOriginal();

        // Campos que no queremos auditar
        $camposExcluir = ['updated_at', 'usuario_id'];

        foreach ($dirty as $campo => $valorNuevo) {
            // Skip excluded fields
            if (in_array($campo, $camposExcluir)) {
                continue;
            }

            // Skip if the value hasn't actually changed
            if (! array_key_exists($campo, $original)) {
                continue;
            }

            $valorAnterior = $original[$campo];

            // Skip if values are the same
            if ($valorAnterior === $valorNuevo) {
                continue;
            }

            // Registrar el cambio
            static::registrarAuditoria(
                documentoTipo: 'producto',
                documentoId: $producto->id,
                accion: 'update',
                campo: $campo,
                valorAnterior: $valorAnterior,
                valorNuevo: $valorNuevo,
                estadoDocumento: $producto->activo ? 'activo' : 'inactivo',
                observacion: "Actualización de campo: {$campo}",
            );
        }
    }

    /**
     * Handle the Producto "created" event.
     */
    public function created(Producto $producto): void
    {
        $producto->refresh();

        static::registrarAuditoria(
            documentoTipo: 'producto',
            documentoId: $producto->id,
            accion: 'create',
            campo: null,
            valorAnterior: null,
            valorNuevo: json_encode([
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'precio_compra' => $producto->precio_compra,
                'precio_venta' => $producto->precio_venta,
            ]),
            estadoDocumento: $producto->activo ? 'activo' : 'inactivo',
            observacion: 'Producto creado',
        );
    }

    /**
     * Handle the Producto "deleted" event.
     */
    public function deleted(Producto $producto): void
    {
        static::registrarAuditoria(
            documentoTipo: 'producto',
            documentoId: $producto->id,
            accion: 'delete',
            campo: null,
            valorAnterior: json_encode([
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
            ]),
            valorNuevo: null,
            estadoDocumento: 'eliminado',
            observacion: 'Producto eliminado',
        );
    }
}
