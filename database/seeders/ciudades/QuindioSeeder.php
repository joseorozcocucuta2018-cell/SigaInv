<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuindioSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'QUINDÍO')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "QUINDÍO" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ARMENIA',
            'BUENAVISTA',
            'CALARCA',
            'CIRCASIA',
            'COATÁ',
            'FILANDIA',
            'GENEVA',
            'LA TEBAIDA',
            'MONTENEGRO',
            'PUERTO RICO',
            'QUIMBAYA',
            'SALONICA',
            'SAN JUAN DE PACAMAL',
            'VILLATURNA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento del Quindío.');
    }
}
