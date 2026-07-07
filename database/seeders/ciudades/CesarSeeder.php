<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CesarSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'CESAR')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "CESAR" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'AGUACHICA',
            'AGUSTÍN CODAZZI',
            'ASTREA',
            'BECERRIL',
            'BOSCONIA',
            'CHIRIGUANA',
            'CODAZZI',
            'CURUMANÍ',
            'EL COPEY',
            'EL PASO',
            'GAMARRA',
            'GONZÁLEZ',
            'LA PAZ',
            'MANAURE',
            'PAILITAS',
            'PELAYA',
            'PUEBLO BELLO',
            'RÍO DE ORO',
            'SAN ALBERTO',
            'SAN DIEGO',
            'SAN JUAN DEL CESAR',
            'TAMALAMEQUE',
            'VALLEDUPAR',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento del Cesar.');
    }
}
