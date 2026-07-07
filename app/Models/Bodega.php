<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BodegaEstado;
use App\Enums\CajaEstado;
use App\Enums\CajaTipo;
use App\Models\Traits\SanitizesAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Bodega extends Model
{
    use HasFactory;
    use SanitizesAttributes;

    protected $table = 'bodegas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'direccion1',
        'direccion2',
        'departamento_id',
        'ciudad_id',
        'estado',
        'usuario_id',
        'es_principal',
        'numero_cajas_pos',
    ];

    protected $casts = [
        'estado' => BodegaEstado::class,
        'es_principal' => 'boolean',
        'numero_cajas_pos' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Bodega $bodega): void {
            if ($bodega->es_principal) {
                static::where('es_principal', true)->update(['es_principal' => false]);
            }
        });

        static::updating(function (Bodega $bodega): void {
            if ($bodega->isDirty('es_principal') && $bodega->es_principal) {
                static::where('id', '!=', $bodega->id)
                    ->where('es_principal', true)
                    ->update(['es_principal' => false]);
            }
        });

        static::saved(function (Bodega $bodega): void {
            if ($bodega->numero_cajas_pos > 0) {
                $bodega->syncPosCajas();
            }
        });
    }

    /**
     * Crear o actualizar la bodega principal cuando la empresa activa "una_sola_bodega"
     */
    public static function crearOActualizarBodegaPrincipal(): ?self
    {
        $empresa = Empresa::actual();

        if (! $empresa || ! $empresa->una_sola_bodega) {
            return null;
        }

        $bodegaExistente = static::where('es_principal', true)->first();

        if ($bodegaExistente) {
            // Actualizar datos de la bodega principal con los de la empresa
            $bodegaExistente->updateQuietly([
                'direccion1' => $empresa->direccion,
                'departamento_id' => $empresa->departamento_id,
                'ciudad_id' => $empresa->ciudad_id,
            ]);

            return $bodegaExistente;
        }

        // Crear la bodega principal
        return static::create([
            'nombre' => 'BODEGA PRINCIPAL',
            'descripcion' => 'Bodega principal creada automáticamente',
            'direccion1' => $empresa->direccion,
            'direccion2' => null,
            'departamento_id' => $empresa->departamento_id,
            'ciudad_id' => $empresa->ciudad_id,
            'estado' => BodegaEstado::ACTIVO,
            'usuario_id' => Auth::id(),
            'es_principal' => true,
        ]);
    }

    /**
     * Obtener la bodega principal
     */
    public static function principal(): ?self
    {
        return static::where('es_principal', true)->first();
    }

    /**
     * Sincroniza las cajas POS asociadas a esta bodega.
     * Crea las que falten según numero_cajas_pos.
     * No elimina cajas existentes (seguridad).
     */
    public function syncPosCajas(): void
    {
        $existentes = Caja::where('nombre', 'like', "{$this->nombre} POS-%")->count();

        if ($this->numero_cajas_pos <= $existentes) {
            return;
        }

        for ($i = $existentes + 1; $i <= $this->numero_cajas_pos; $i++) {
            Caja::create([
                'nombre' => "{$this->nombre} POS-{$i}",
                'tipo' => CajaTipo::POS,
                'saldo_inicial' => 0,
                'estado' => CajaEstado::ACTIVA,
                'activo' => true,
                'usuario_id' => $this->usuario_id ?? Auth::id(),
            ]);
        }
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
