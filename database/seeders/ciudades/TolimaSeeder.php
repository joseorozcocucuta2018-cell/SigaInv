<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TolimaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'TOLIMA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "TOLIMA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ALVARADO',
            'AMBALEMA',
            'ANZOÁTEGUI',
            'ARMENIA',
            'ATACO',
            'CAJAMARCA',
            'CARRILLO',
            'CASABIANCA',
            'CHAPARRAL',
            'COYAIMA',
            'CUNDAY',
            'DOLORES',
            'ESPINAL',
            'FALAN',
            'FLANDES',
            'FRESNO',
            'GUAMO',
            'HERVEO',
            'HONDA',
            'IBAGUÉ',
            'ICONONZO',
            'LÉRIDA',
            'LIBANO',
            'MARIQUITA',
            'MELGAR',
            'MURILLO',
            'NATAGAIMA',
            'ORTEGA',
            'PALOCABILDO',
            'PRAEDO',
            'PURIFICACIÓN',
            'RICAURTE',
            'ROLDANILLO',
            'RONCESVALLES',
            'SAN ANTONIO',
            'SAN LUIS',
            'SANTA ISABEL',
            'SUAREZ',
            'VALLE DE SAN JOSÉ',
            'VENADAS',
            'VILLAHERMOSA',
            'VILLARRICA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento del Tolima.');
    }
}
