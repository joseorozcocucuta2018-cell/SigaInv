<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AuditoriaOperacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditorias';

    protected $fillable = [
        'usuario_id',
        'tabla',
        'operacion',
        'registro_id',
        'datos_anteriores',
        'datos_nuevos',
        'fecha_operacion',
    ];

    protected $casts = [
        'operacion' => AuditoriaOperacion::class,
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'fecha_operacion' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
