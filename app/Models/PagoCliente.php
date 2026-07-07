<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasAutoNumbering;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PagoCliente extends Model
{
    use HasAutoNumbering;

    protected string $autoNumberPrefix = 'RC-';

    protected int $autoNumberOffset = 3;

    protected $table = 'pago_clientes';

    protected $fillable = [
        'numero',
        'cliente_id',
        'forma_pago_id',
        'banco_id',
        'caja_id',
        'usuario_id',
        'fecha',
        'monto',
        'referencia',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'monto' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePagoCliente::class, 'pago_cliente_id');
    }

    public function formaPago(): BelongsTo
    {
        return $this->belongsTo(FormaPago::class, 'forma_pago_id');
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
