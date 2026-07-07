<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuainiaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'GUAINÍA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "GUAINÍA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'INÍRIDA',
            'BARRANCO MINAS',
            'MAPIRIPANA',
            'SAN FELIPE',
            'PUERTO COLOMBIA',
            'LA GUADALUPE',
            'CACHIRÚ',
            'PANA PANA',
            'PUERTO SÁNCHEZ',
            'TAMARA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Guainía.');
    }
}
