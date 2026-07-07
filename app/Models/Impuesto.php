<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ImpuestoTipo;
use App\Models\Traits\SanitizesAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Impuesto extends Model
{
    use HasFactory;
    use SanitizesAttributes;

    protected $table = 'impuestos';

    protected $fillable = [
        'nombre',
        'tipo',
        'porcentaje',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'tipo' => ImpuestoTipo::class,
        'activo' => 'boolean',
        'porcentaje' => 'decimal:2',
    ];
}
