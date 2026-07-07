<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotaEstado;
use App\Enums\NotaTipo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nota extends Model
{
    use HasFactory;

    protected $table = 'notas';

    protected $fillable = [
        'tipo',
        'numero',
        'venta_id',
        'cliente_id',
        'fecha',
        'motivo',
        'subtotal',
        'impuestos',
        'total',
        'estado',
        'confirmada_en',
        'usuario_id',
        'xml_path',
        'pdf_path',
    ];

    protected $casts = [
        'tipo' => NotaTipo::class,
        'estado' => NotaEstado::class,
        'fecha' => 'datetime',
        'confirmada_en' => 'datetime',
        'subtotal' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleNota::class);
    }
}
