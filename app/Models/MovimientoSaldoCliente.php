<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MovimientoSaldoClienteTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoSaldoCliente extends Model
{
    protected $table = 'movimientos_saldo_cliente';

    protected $fillable = [
        'cliente_id',
        'tipo',
        'referencia',
        'monto',
        'saldo_anterior',
        'saldo_nuevo',
        'descripcion',
        'usuario_id',
    ];

    protected $casts = [
        'tipo' => MovimientoSaldoClienteTipo::class,
        'monto' => 'decimal:2',
        'saldo_anterior' => 'decimal:2',
        'saldo_nuevo' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
