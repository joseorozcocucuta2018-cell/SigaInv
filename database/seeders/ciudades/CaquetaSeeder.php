<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaquetaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'CAQUETÁ')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "CAQUETÁ" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ALBANIA',
            'BELEN DE LOS ANDAQUIES',
            'BELÉN',
            'BOYACÁ',
            'CURILLO',
            'EL DONCELLO',
            'EL PAUJIL',
            'FLORENCIA',
            'LA MONTAÑITA',
            'MORELIA',
            'PUERTO RICO',
            'SAN JOSÉ DEL FRAGUA',
            'SAN VICENTE DEL CAGUÁN',
            'SOLANO',
            'SOLARTA',
            'VALPARAÍSO',
            'VISTAHERMOSA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Caquetá.');
    }
}
