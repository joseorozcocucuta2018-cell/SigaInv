<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmpresaRegimenTributario;
use App\Enums\EmpresaTipoPersona;
use App\Models\Traits\SanitizesAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Empresa extends Model
{
    use HasFactory;
    use SanitizesAttributes;

    protected $table = 'empresa';

    protected $fillable = [
        'razon_social',
        'nombre_comercial',
        'nit',
        'digito_verificacion',
        'tipo_persona',
        'regimen_tributario',
        'responsable_iva',
        'usa_seriales',
        'una_sola_bodega',
        'actividad_ciiu',
        'direccion',
        'departamento_id',
        'ciudad_id',
        'pais',
        'telefono',
        'celular',
        'email',
        'email_documentos',
        'sitio_web',
        'resolucion_dian',
        'resolucion_fecha_expedicion',
        'resolucion_fecha_vencimiento',
        'resolucion_desde',
        'resolucion_hasta',
        'logo',
        'logo_pos',
        'logo_impresion',
        'pie_pagina',
        'notas_factura',
        'margen_ganancia_default',
        'margen_ganancia_minimo',
    ];

    protected $casts = [
        'tipo_persona' => EmpresaTipoPersona::class,
        'regimen_tributario' => EmpresaRegimenTributario::class,
        'responsable_iva' => 'boolean',
        'usa_seriales' => 'boolean',
        'una_sola_bodega' => 'boolean',
        'digito_verificacion' => 'integer',
        'margen_ganancia_default' => 'decimal:2',
        'margen_ganancia_minimo' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saved(function (Empresa $empresa): void {
            if ($empresa->una_sola_bodega) {
                Bodega::crearOActualizarBodegaPrincipal();
            }
        });
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class);
    }

    public static function actual(): ?self
    {
        return static::query()->first();
    }

    public static function usaSeriales(): bool
    {
        return (bool) optional(static::actual())->usa_seriales;
    }

    public static function isResponsableIva(): bool
    {
        return (bool) optional(static::actual())->responsable_iva;
    }

    public static function usaUnaSolaBodega(): bool
    {
        return (bool) optional(static::actual())->una_sola_bodega;
    }

    public static function getBodegaPrincipalId(): ?int
    {
        if (static::usaUnaSolaBodega()) {
            $bodega = Bodega::principal();
            if ($bodega) {
                return $bodega->id;
            }
        }

        return null;
    }
}
