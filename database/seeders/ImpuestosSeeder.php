<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ImpuestosSeeder
 *
 * Impuestos vigentes en Colombia según normativa DIAN.
 *
 * IVA (Impuesto al Valor Agregado):
 *   - 0%  : bienes exentos y excluidos (alimentos, medicamentos, etc.)
 *   - 5%  : tasa diferencial (algunos alimentos, equipos médicos, etc.)
 *   - 19% : tasa general
 *
 * INC (Impuesto Nacional al Consumo):
 *   - 4%  : telefonía, datos, navegación
 *   - 8%  : restaurantes, bares
 *   - 16% : vehículos, aeronaves, embarcaciones
 *
 * Retenciones en la fuente (aproximaciones — varían por actividad y cuantía):
 *   - ReteRenta 3.5% : servicios en general
 *   - ReteRenta 4%   : arrendamientos
 *   - ReteRenta 11%  : honorarios y comisiones
 *   - ReteIVA 15%    : retención sobre IVA (aplica sobre el valor del IVA)
 *   - ReteICA 0.414%: comercio en Cúcuta (varía por municipio y actividad)
 */
class ImpuestosSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $impuestos = [
            // IVA
            ['nombre' => 'IVA 0%',           'tipo' => 'IVA',        'porcentaje' => 0.00, 'descripcion' => 'IVA tarifa 0% — bienes exentos y excluidos'],
            ['nombre' => 'IVA 5%',           'tipo' => 'IVA',        'porcentaje' => 5.00, 'descripcion' => 'IVA tarifa diferencial 5%'],
            ['nombre' => 'IVA 19%',          'tipo' => 'IVA',        'porcentaje' => 19.00, 'descripcion' => 'IVA tarifa general 19%'],

            // INC
            ['nombre' => 'INC 4%',           'tipo' => 'INC',        'porcentaje' => 4.00, 'descripcion' => 'Impuesto Nacional al Consumo 4% — telefonía y datos'],
            ['nombre' => 'INC 8%',           'tipo' => 'INC',        'porcentaje' => 8.00, 'descripcion' => 'Impuesto Nacional al Consumo 8% — restaurantes'],
            ['nombre' => 'INC 16%',          'tipo' => 'INC',        'porcentaje' => 16.00, 'descripcion' => 'Impuesto Nacional al Consumo 16% — vehículos'],

            // Retenciones
            ['nombre' => 'ReteRenta 3.5%',   'tipo' => 'ReteRenta',  'porcentaje' => 3.50, 'descripcion' => 'Retención en la fuente 3.5% — servicios en general'],
            ['nombre' => 'ReteRenta 4%',     'tipo' => 'ReteRenta',  'porcentaje' => 4.00, 'descripcion' => 'Retención en la fuente 4% — arrendamientos'],
            ['nombre' => 'ReteRenta 11%',    'tipo' => 'ReteRenta',  'porcentaje' => 11.00, 'descripcion' => 'Retención en la fuente 11% — honorarios y comisiones'],
            ['nombre' => 'ReteIVA 15%',      'tipo' => 'ReteIVA',    'porcentaje' => 15.00, 'descripcion' => 'Retención de IVA 15% — aplica sobre el valor del IVA'],
            ['nombre' => 'ReteICA 0.414%',   'tipo' => 'ReteICA',    'porcentaje' => 0.414, 'descripcion' => 'Retención ICA Cúcuta — comercio (verificar tarifa vigente)'],
        ];

        foreach ($impuestos as $impuesto) {
            $existe = DB::table('impuestos')
                ->where('nombre', $impuesto['nombre'])
                ->exists();

            if (! $existe) {
                DB::table('impuestos')->insert([
                    'nombre' => $impuesto['nombre'],
                    'tipo' => $impuesto['tipo'],
                    'porcentaje' => $impuesto['porcentaje'],
                    'descripcion' => $impuesto['descripcion'],
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
