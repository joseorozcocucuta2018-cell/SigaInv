<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\NumeracionEstado;
use App\Enums\NumeracionTipoDocumento;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NumeracionesSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('numeraciones')->count() > 0) {
            $this->command->info('NumeracionesSeeder omitido — ya existen registros.');

            return;
        }

        $now = Carbon::now();
        $anno = (int) $now->format('Y');

        DB::table('numeraciones')->insert([
            [
                'tipo_documento' => NumeracionTipoDocumento::REMISION->value,
                'prefijo' => 'RE',
                'resolucion_numero' => null,
                'resolucion_fecha_expedicion' => null,
                'resolucion_fecha_vencimiento' => null,
                'observaciones' => 'Numeración de remisiones',
                'consecutivo_desde' => 1,
                'consecutivo_hasta' => 9999999,
                'consecutivo_actual' => 0,
                'anno' => $anno,
                'estado' => NumeracionEstado::ACTIVO->value,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_documento' => NumeracionTipoDocumento::COTIZACION->value,
                'prefijo' => 'COT',
                'resolucion_numero' => null,
                'resolucion_fecha_expedicion' => null,
                'resolucion_fecha_vencimiento' => null,
                'observaciones' => 'Numeración de cotizaciones',
                'consecutivo_desde' => 1,
                'consecutivo_hasta' => 9999999,
                'consecutivo_actual' => 0,
                'anno' => $anno,
                'estado' => NumeracionEstado::ACTIVO->value,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
