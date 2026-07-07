<?php

namespace Database\Seeders;

use App\Enums\BodegaEstado;
use App\Models\Bodega;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * BodegasSeeder
 *
 * Crea o actualiza la bodega principal por defecto (id=1).
 * Todo sistema necesita al menos una bodega para poder registrar
 * movimientos de inventario, ventas y compras.
 *
 * Los valores de ciudad y departamento apuntan a Cúcuta / Norte de Santander
 * (IDs DANE oficiales del coredata) — ajustar según ubicación real.
 *
 * Si la empresa tiene `una_sola_bodega = true`, se sincronizan los datos
 * de dirección con los de la empresa.
 */
class BodegasSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $usuarioId = DB::table('users')->value('id');

        $empresa = DB::table('empresa')->first();

        $bodegaExistente = Bodega::where('es_principal', true)->first();

        if ($bodegaExistente) {
            // Actualizar datos si la empresa tiene una sola bodega
            if ($empresa && $empresa->una_sola_bodega) {
                $bodegaExistente->updateQuietly([
                    'direccion1' => $empresa->direccion,
                    'departamento_id' => $empresa->departamento_id,
                    'ciudad_id' => $empresa->ciudad_id,
                ]);
            }
        } else {
            // Crear bodega principal
            Bodega::create([
                'nombre' => 'BODEGA PRINCIPAL',
                'descripcion' => 'Bodega principal de la empresa',
                'direccion1' => $empresa?->direccion ?? 'SIN INFORMACION',
                'direccion2' => null,
                'departamento_id' => $empresa?->departamento_id ?? 54,
                'ciudad_id' => $empresa?->ciudad_id ?? 889,
                'estado' => BodegaEstado::ACTIVO,
                'es_principal' => true,
                'usuario_id' => $usuarioId,
            ]);
        }
    }
}
