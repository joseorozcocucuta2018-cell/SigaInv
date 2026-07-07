<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\MovimientoInventario;

class MovimientoInventarioObserver
{
    /**
     * Handle the MovimientoInventario "created" event.
     */
    public function created(MovimientoInventario $movimientoInventario): void
    {
        $producto = $movimientoInventario->producto;

        if ($producto && ! $producto->tiene_movimientos) {
            $producto->update(['tiene_movimientos' => true]);
        }
    }

    /**
     * Handle the MovimientoInventario "updated" event.
     */
    public function updated(MovimientoInventario $movimientoInventario): void
    {
        //
    }

    /**
     * Handle the MovimientoInventario "deleted" event.
     */
    public function deleted(MovimientoInventario $movimientoInventario): void
    {
        //
    }

    /**
     * Handle the MovimientoInventario "restored" event.
     */
    public function restored(MovimientoInventario $movimientoInventario): void
    {
        //
    }

    /**
     * Handle the MovimientoInventario "force deleted" event.
     */
    public function forceDeleted(MovimientoInventario $movimientoInventario): void
    {
        //
    }
}
