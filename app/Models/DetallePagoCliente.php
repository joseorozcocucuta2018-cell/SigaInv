<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePagoCliente extends Model
{
    protected $table = 'detalle_pago_clientes';

    protected $fillable = [
        'pago_cliente_id',
        'documento_tipo',
        'documento_id',
        'monto_aplicado',
    ];

    protected $casts = [
        'monto_aplicado' => 'decimal:2',
    ];

    public function pagoCliente(): BelongsTo
    {
        return $this->belongsTo(PagoCliente::class, 'pago_cliente_id');
    }

    /**
     * Retorna el documento asociado (Venta o Remision).
     */
    public function documento(): ?Model
    {
        return match ($this->documento_tipo) {
            'venta' => Venta::find($this->documento_id),
            'remision' => Remision::find($this->documento_id),
            default => null,
        };
    }
}
