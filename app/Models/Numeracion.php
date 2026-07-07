<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NumeracionEstado;
use App\Enums\NumeracionTipoDocumento;
use Illuminate\Database\Eloquent\Model;

class Numeracion extends Model
{
    protected $table = 'numeraciones';

    protected $fillable = [
        'tipo_documento',
        'prefijo',
        'resolucion_numero',
        'resolucion_fecha_expedicion',
        'resolucion_fecha_vencimiento',
        'observaciones',
        'consecutivo_desde',
        'consecutivo_hasta',
        'consecutivo_actual',
        'anno',
        'estado',
    ];

    protected $casts = [
        'tipo_documento' => NumeracionTipoDocumento::class,
        'estado' => NumeracionEstado::class,
        'consecutivo_desde' => 'integer',
        'consecutivo_hasta' => 'integer',
        'consecutivo_actual' => 'integer',
        'anno' => 'integer',
        'resolucion_fecha_expedicion' => 'date',
        'resolucion_fecha_vencimiento' => 'date',
    ];
}
